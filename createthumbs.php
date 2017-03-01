<?PHP
//Script for generating 'thumbs' for SimpleImage.

namespace abeautifulsite;
use Exception;

//test for the environment ... CLI versus browser
if (PHP_SAPI === 'cli') {
$reldir = $argv[1];

if (!isset($argv[1]))
    exit("Must specify a directory to scan\n");

if (!is_dir($argv[1]))
    exit($argv[1]."' is not a directory\n");
} else {
die();
}
  
require 'SimpleImage.php';
  
$img = new SimpleImage();


$path_thumbs = $reldir . 'thumbs/';
$path_imgs = $reldir;

if (!is_dir($path_thumbs)) {
mkdir($path_thumbs);
}

$allowed = array( 'gif', 'jpg', 'jpeg', 'png', 'ttf' );

$dir_imgs = new \DirectoryIterator($path_imgs);
foreach ($dir_imgs as $fileinfo) {
if (!$fileinfo->isDot() && $fileinfo->isFile() && in_array($fileinfo->getExtension(), $allowed)) {
$name = $fileinfo->getFilename();
$pathname = $path_imgs . $fileinfo->getFilename();
		      
echo $name . "\n";
try {
$img->load($pathname)->fit_to_height(300)->save($path_thumbs . $name);
}
catch (Exception $e) {
echo '<span style="color: red;">'.$e->getMessage().'</span>';
}
}
}
// Local Variables:
// firestarter: "gist -u a80d7b60361c786afeba %p"
// End:
?>
