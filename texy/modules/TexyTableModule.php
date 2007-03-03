<?php

/**
 * Texy! universal text -> html converter
 * --------------------------------------
 *
 * This source file is subject to the GNU GPL license.
 *
 * @author     David Grudl aka -dgx- <dave@dgx.cz>
 * @link       http://texy.info/
 * @copyright  Copyright (c) 2004-2007 David Grudl
 * @license    GNU GENERAL PUBLIC LICENSE v2
 * @package    Texy
 * @category   Text
 * @version    $Revision$ $Date$
 */

// security - include texy.php, not this file
if (!defined('TEXY')) die();



/**
 * Table module
 */
class TexyTableModule extends TexyModule
{
    protected $default = array('table' => TRUE);

    /** @var string  CSS class for odd rows */

    public $oddClass;
    /** @var string  CSS class for even rows */
    public $evenClass;

    private $isHead;
    private $colModifier;
    private $last;
    private $row;



    public function init()
    {
        $this->texy->registerBlockPattern(
            array($this, 'processBlock'),
            '#^(?:'.TEXY_MODIFIER_HV.'\n)?'   // .{color: red}
          . '\|.*()$#mU',                     // | ....
            'table'
        );
    }



    /**
     * Callback function (for blocks)
     *
     *  .(title)[class]{style}>
     *  |------------------
     *  | xxx | xxx | xxx | .(..){..}[..]
     *  |------------------
     *  | aa  | bb  | cc  |
     */
    public function processBlock($parser, $matches)
    {
        list(, $mMod) = $matches;
        //    [1] => .(title)[class]{style}<>_

        $tx = $this->texy;

        $el = TexyHtml::el('table');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $el);

        $parser->moveBackward();

        if ($parser->receiveNext('#^\|(\#|\=){2,}(?!\\1)(.*)\\1*\|? *'.TEXY_MODIFIER_H.'?()$#Um', $matches)) {
            list(, , $mContent, $mMod) = $matches;
            //    [1] => # / =
            //    [2] => ....
            //    [3] => .(title)[class]{style}<>

            $caption = TexyHtml::el('caption');
            $mod = new TexyModifier($mMod);
            $mod->decorate($tx, $caption);
            $caption->parseLine($tx, $mContent);
            $el->childNodes[] = $caption;
        }

        $this->isHead = FALSE;
        $this->colModifier = array();
        $this->last = array();
        $this->row = 0;

        while (TRUE) {
            if ($parser->receiveNext('#^\|\-{3,}$#Um', $matches)) {
                $this->isHead = !$this->isHead;
                continue;
            }

            if ($elRow = $this->processRow($parser)) {
                $el->childNodes[] = $elRow;
                $this->row++;
                continue;
            }

            break;
        }

        $parser->children[] = $el;
    }



    protected function processRow($parser)
    {
        $tx = $this->texy;

        if (!$parser->receiveNext('#^\|(.*)(?:|\|\ *'.TEXY_MODIFIER_HV.'?)()$#U', $matches)) {
            return FALSE;
        }
        list(, $mContent, $mMod) = $matches;
        //    [1] => ....
        //    [2] => .(title)[class]{style}<>_

        $elRow = TexyHtml::el('tr');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $elRow);

        if ($this->row % 2 === 0) {
            if ($this->oddClass) $elRow->class[] = $this->oddClass;
        } else {
            if ($this->evenClass) $elRow->class[] = $this->evenClass;
        }

        $col = 0;
        $elField = NULL;

        // special escape sequence \|
        $mContent = str_replace('\\|', '&#x7C;', $mContent);

        foreach (explode('|', $mContent) as $field) {
            if (($field == '') && $elField) { // colspan
                $elField->colspan++;
                unset($this->last[$col]);
                $col++;
                continue;
            }

            $field = rtrim($field);
            if ($field === '^') { // rowspan
                if (isset($this->last[$col])) {
                    $this->last[$col]->rowspan++;
                    $col += $this->last[$col]->colspan;
                    continue;
                }
            }

            if (!preg_match('#(\*??)\ *'.TEXY_MODIFIER_HV.'??(.*)'.TEXY_MODIFIER_HV.'?()$#AU', $field, $matches)) continue;
            list(, $mHead, $mModCol, $mContent, $mMod) = $matches;
            //    [1] => * ^
            //    [2] => .(title)[class]{style}<>_
            //    [3] => ....
            //    [4] => .(title)[class]{style}<>_

            if ($mModCol) {
                $this->colModifier[$col] = new TexyModifier($mModCol);
            }

            if (isset($this->colModifier[$col]))
                $mod = clone $this->colModifier[$col];
            else
                $mod = new TexyModifier;

            $mod->setProperties($mMod);

            $elField = new TexyTableFieldElement;
            $elField->elName = $this->isHead || ($mHead === '*') ? 'th' : 'td';
            $mod->decorate($tx, $elField);

            $elField->parseLine($tx, $mContent);
            if ($elField->childNodes[0] === '') $elField->childNodes[0]  = "\xC2\xA0"; // &nbsp;

            $elRow->childNodes[] = $elField;
            $this->last[$col] = $elField;
            $col++;
        }

        return $elRow;
    }

} // TexyTableModule




/**
 * Table field TD / TH
 */
class TexyTableFieldElement extends TexyHtml
{
    public $colspan = 1;
    public $rowspan = 1;


    public function startTag()
    {
        if ($this->colspan == 1) $this->colspan = NULL;
        if ($this->rowspan == 1) $this->rowspan = NULL;
        return parent::startTag();
    }

} // TexyTableFieldElement