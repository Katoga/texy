<?php

/**
 * TEXY! HEADINGS DEMO
 * --------------------------------------
 *
 * This source file is subject to the GNU GPL license.
 *
 * @link       http://www.texy.info/
 * @author     David Grudl aka -dgx- <dave@dgx.cz>
 * @copyright  Copyright (c) 2004-2006 David Grudl
 * @license    GNU GENERAL PUBLIC LICENSE
 */



// include Texy!
require_once dirname(__FILE__).'/../../texy/texy.php';



$texy = &new Texy();
$text = file_get_contents('sample.texy');


// 1) Dynamic method

$texy->headingModule->top       = 2;   // set headings top limit
$texy->headingModule->balancing = TEXY_HEADING_DYNAMIC; // this is default

$html = $texy->process($text);  // that's all folks!

// echo topmost heading (text is html safe!)
echo '<title>' . $texy->headingModule->title . '</title>';

// and echo generated HTML code
echo '<strong>Dynamic method:</strong>';
echo '<pre>';
echo htmlSpecialChars($html);
echo '</pre>';
echo '<hr />';




// 2) Fixed method

$texy->headingModule->top       = 1;   // set headings top limit
$texy->headingModule->balancing = TEXY_HEADING_FIXED;

$html = $texy->process($text);  // that's all folks!

// and echo generated HTML code
echo '<strong>Fixed method:</strong>';
echo '<pre>';
echo htmlSpecialChars($html);
echo '</pre>';
echo '<hr />';




// 3) User-defined fixed method

$texy->headingModule->top       = 1;   // set headings top limit
$texy->headingModule->balancing = TEXY_HEADING_FIXED;
$texy->headingModule->levels['='] = 0;  // = means 0; top=1;       0 + 1 = 1 (h1)
$texy->headingModule->levels['-'] = 1;  // - means 1; top=1;       1 + 1 = 2 (h2)
$texy->headingModule->levels[5] = 2;    // ##### means 2; top=1;   2 + 1 = 3 (h3)

$html = $texy->process($text);  // that's all folks!

// and echo generated HTML code
echo '<strong>User-defined fixed method:</strong>';
echo '<pre>';
echo htmlSpecialChars($html);
echo '</pre>';
echo '<hr />';

?>