<?php

/**
 * --------------------------
 *   TEXY! USER IMAGES DEMO
 * --------------------------
 *
 * Copyright (c) 2004-2005, David Grudl <dave@dgx.cz>. All rights reserved.
 * Web: http://www.texy.info/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */


/**
 *  This demo shows how Texy! control images (useful for CMS)
 *     - programmable images controlling
 *     - onMouseOver state
 *     - support for preloading
 */


$libs_path = '../../texy/';
$texy_path = $libs_path;


// include Texy!
require_once($texy_path . 'texy.php');



// this is user callback function for processing 'image references' [*xxxx*]
// returns false or TexyImageReference

function &myUserFunc(&$texy, $refName) {
  if ($refName != 'user')  // accept only [* user *]
    return false;

  $elRef = &new TexyImageReference($texy);
  $elRef->URLs = 'image.gif | '        // image URL
               . 'image-over.gif | '   // onmouseover image
               . 'image-big.gif';      // linked image
  $elRef->modifier->title = 'Texy! logo';
  return $elRef;
}



$texy = &new Texy();
$texy->images->userReferences = 'myUserFunc';


// processing
$text = file_get_contents('sample.texy');
$html = $texy->process($text);  // that's all folks!


// echo formated output
echo $html;






// echo all used images
echo '<hr />';
echo '<pre>';
echo 'used images:';
print_r($texy->summary->images);
echo 'onmouseover images:';
print_r($texy->summary->preload);
echo '</pre>';


// build preload script!
$script = "var preloadImg = new Array();\n";
foreach ($texy->summary->preload as $key => $image)
  $script .= "preloadImg[$key] = new Image(); preloadImg[$key].src='".htmlSpecialChars($image, ENT_QUOTES)."';\n";

echo '<pre>';
echo $script;
echo '</pre>';






// and echo generated HTML code
echo '<hr />';
echo '<pre>';
echo htmlSpecialChars($html);
echo '</pre>';

?>