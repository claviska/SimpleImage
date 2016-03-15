<?php
namespace   abeautifulsite;
use         Exception;

require '../src/abeautifulsite/SimpleImage.php';

// output a cropped image directly to browser given a focal point
if(isset($_GET['focal'])) {
	$img = new SimpleImage($_GET['image']);
    $img->thumbnail(90, 90, $_GET['focal'])->output('jpg');
    exit;
}




//request some image crops to this page


$focal_points = array(
	'top',
	'bottom',
	'left',
	'right',
	'top left',
	'top right',
	'bottom left',
	'bottom right',
	'center',
);
$images = array(
	'butterfly.jpg',
	'tower.jpg'
);


?>
<!doctype html>

<?php foreach ($images as $image): ?>
	<div style="width: 300px; float: left;">
		<?php foreach ($focal_points as $focal) :?>
			<div><img src="?focal=<?php echo $focal ?>&amp;image=<?php echo $image ?>" alt="<?php echo $focal ?>"> <?php echo $focal ?></div>
		<?php endforeach; ?>
	</div>
<?php endforeach ?>