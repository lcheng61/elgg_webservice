<?php
/**
 * Ideas helper functions
 *
 * @package Ideas
 */


/**
 * Pull together ideas variables for the save/edit form
 *
 * @param Ideas $post
 * @return array
 */
function ideas_prepare_form_vars($post = NULL) {

	$values = array(
		'title' => NULL,
		'description' => NULL,
		'video' => NULL,
		'access_id' => ACCESS_DEFAULT,
		'ideascategory' => NULL,
		'ideas_type' => NULL,
		'custom' => NULL,
		'tags' => NULL,
		'container_guid' => elgg_get_page_owner_guid(),
		'guid' => NULL,
	);

	if ($post) {
		foreach (array_keys($values) as $field) {
			if (isset($post->$field)) {
				$values[$field] = $post->$field;
			}
		}
	}

	if (elgg_is_sticky_form('ideas')) {
		$sticky_values = elgg_get_sticky_values('ideas');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}
	
	elgg_clear_sticky_form('ideas');

	if (!$post) {
		return $values;
	}

	return $values;
}

/**
 * Delete ideas post and pictures
 *
 * @param Ideas $post
 * @return array
 */
function ideas_delete_post($post = NULL) {

	if (!$post) {
		return false;
	}

	// Get owning user
	$owner = get_entity($post->getOwner());
	$owner_guid = $owner->guid;

	// Delete the images
	$prefix = "ideas/".$guid;
		
	$small = $prefix."small.jpg";
	$medium = $prefix."medium.jpg";
	$large = $prefix."large.jpg";
	$master = $prefix."master.jpg";
	$original = $prefix.".jpg";
				
	if ($small) {
		$delfile = new ElggFile();
		$delfile->owner_guid = $owner_guid;
		$delfile->setFilename($small);
		$delfile->delete();
	}

	if ($medium) {
		$delfile = new ElggFile();
		$delfile->owner_guid = $owner_guid;
		$delfile->setFilename($medium);
		$delfile->delete();
	}

	if ($large) {
		$delfile = new ElggFile();
		$delfile->owner_guid = $owner_guid;
		$delfile->setFilename($large);
		$delfile->delete();
	}

	if ($master) {
		$delfile = new ElggFile();
		$delfile->owner_guid = $owner_guid;
		$delfile->setFilename($master);
		$delfile->delete();
	}

	if ($original) {
		$delfile = new ElggFile();
		$delfile->owner_guid = $owner_guid;
		$delfile->setFilename($original);
		$delfile->delete();
	}


	// Delete the ideas post
	$rowsaffected = $post->delete();
	if ($rowsaffected > 0) {
		// Success
		return true;
	} else {
		// Error
		return false;
	}

}

/**
 * Add images to ideas post
 *
 * @param Ideas $post
 * @return array
 */
function ideas_add_image($post = NULL, $data = NULL, $imagenum = 0) {

	if (!$post || !$data) {
		return false;
	}

	$filenum = $imagenum;
	if ($imagenum == 1) {
		$filenum = '';
	}

	$prefix = "ideas/".$post->guid;
		
	$filehandler = new ElggFile();
	$filehandler->owner_guid = $post->owner_guid;
	$filehandler->setFilename($prefix . $filenum . ".jpg");
	$filehandler->open("write");
	$filehandler->write($data);
	$filehandler->close();
		
	$small = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),40,40, true);
	$medium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),153,153, true);
	$large = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),200,200, false);
	$master = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),600,800, false);

	if ($small) {
	
		$sizes = array('small' => $small, 'medium' => $medium, 'large' => $large, 'master' => $master);
		foreach($sizes as $size => $imgdata) {
			
				$thumb = new ElggFile();
				$thumb->owner_guid = $post->owner_guid;
				$thumb->setMimeType('image/jpeg');
				$thumb->setFilename($prefix.$size.$filenum.'.jpg');
				$thumb->open('write');
				$thumb->write($imgdata);
				$thumb->close();
		}
		
		// Set image in metadata array
		ideas_set_images($post, $imagenum);
	}
}

/**
 * Set what images has been uploaded (1,2,3, or 4)
 *
 * @param Ideas $post
 * @return array
 */
function ideas_set_images($post, $imagenum) {

	// Check images metadata, if empty create initial array
	if ($post->images == '') {
		$post->images = serialize(array(1 => 0, 2 => 0, 3 => 0, 4 => 0));
	}
	
	// Create image array and seialize it into metadata
	$images = unserialize($post->images);
	$new_array = array();
	foreach ($images as $key => $value) {
		if ($key == $imagenum) {
			$value = 1;
		}
		$new_array[$key] = $value;
	}
	$post->images = serialize($new_array);
	return true;
}

/**
 * Delete ideas post and pictures
 *
 * @param Ideas $post
 * @return array
 */
function ideas_delete_image($post = NULL, $imagenum) {

	if (!$post || !$imagenum) {
		return false;
	}

	$filenum = $imagenum;
	if ($imagenum == 1) {
		$filenum = '';
	}

	$owner = get_entity($post->getOwner());
	$owner_guid = $owner->guid;
	$prefix = "ideas/{$post->guid}";

	$names = array("{$prefix}small{$filenum}.jpg", "{$prefix}medium{$filenum}.jpg", "{$prefix}large{$filenum}.jpg", "{$prefix}master{$filenum}.jpg");
	foreach($names as $name) {
		$delfile = new ElggFile();
		$delfile->owner_guid = $owner_guid;
		$delfile->setFilename($name);
		$delfile->delete();
	}

	$images = unserialize($post->images);
	$new_array = array();
	foreach ($images as $key => $value) {
		if ($key == $imagenum) {
			$value = 0;
		}
		$new_array[$key] = $value;
	}
	$post->images = serialize($new_array);
	$post->save();
	return true;
}