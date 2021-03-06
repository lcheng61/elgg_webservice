<?php
/**
 * Market helper functions
 *
 * @package Market
 */


/**
 * Pull together market variables for the save/edit form
 *
 * @param Market $post
 * @return array
 */
function market_prepare_form_vars($post = NULL) {

	$values = array(
		'title' => NULL,
		'description' => NULL,
		'price' => NULL,
		'sold_count' => NULL,
		'access_id' => ACCESS_DEFAULT,
		'marketcategory' => NULL,
		'market_type' => NULL,
		'custom' => NULL,
		'tags' => NULL,
		'container_guid' => elgg_get_page_owner_guid(),
		'guid' => NULL,
		'tips_number' => NULL, // number of tips for this product
		'tips' => NULL,  // related tips (array) to this product
                'images_urls' => NULL, // array of image URLs
	);

	if ($post) {
		foreach (array_keys($values) as $field) {
			if (isset($post->$field)) {
				$values[$field] = $post->$field;
			}
		}
	}

	if (elgg_is_sticky_form('market')) {
		$sticky_values = elgg_get_sticky_values('market');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}
	
	elgg_clear_sticky_form('market');

	if (!$post) {
		return $values;
	}

	return $values;
}

/**
 * Delete market post and pictures
 *
 * @param Market $post
 * @return array
 */
function market_delete_post($post = NULL) {

	if (!$post) {
		return false;
	}

	// Get owning user
	$owner = get_entity($post->getOwner());
	$owner_guid = $owner->guid;

	// Delete the images
	$prefix = "market/".$guid;
		
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


	// Delete the market post
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
 * Add images to market post
 *
 * @param Market $post
 * @return array
 */
function market_add_image($post = NULL, $data = NULL, $imagenum = 0) {

	if (!$post || !$data) {
		return false;
	}

	$filenum = $imagenum;
	if ($imagenum == 1) {
		$filenum = '';
	}

	$prefix = "market/".$post->guid;
		
	$filehandler = new ElggFile();
	$filehandler->owner_guid = $post->owner_guid;
	$filehandler->setFilename($prefix . $filenum . ".jpg");
	$filehandler->open("write");
	$filehandler->write($data);
	$filehandler->close();
		
//	$small = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),40,40, true);
//	$medium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),153,153, true);
//	$large = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),200,200, false);
//	$master = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),600,800, false);

        // for better display on APP
	$small = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),40,61, true);
	$medium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),153,233, true);
	$large = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),210,320, false);
	$master = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),420,640, false);

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
		market_set_images($post, $imagenum);
	}
}

/**
 * Set what images has been uploaded (1,2,3, or 4)
 *
 * @param Market $post
 * @return array
 */
function market_set_images($post, $imagenum) {

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
 * Delete market post and pictures
 *
 * @param Market $post
 * @return array
 */
function market_delete_image($post = NULL, $imagenum) {

	if (!$post || !$imagenum) {
		return false;
	}

	$filenum = $imagenum;
	if ($imagenum == 1) {
		$filenum = '';
	}

	$owner = get_entity($post->getOwner());
	$owner_guid = $owner->guid;
	$prefix = "market/{$post->guid}";

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

/**
 * Add one tip to a product post.
 *
 * @param Market $post
 * @return if we can add a tip
 */
function market_add_tip($post = NULL, $tip_id = 0) {

    if (!$post || $tip_id == 0) {
	return false;
    }

	// Check tips metadata, if empty create initial array
    if ($post->tips == '') {
	$post->tips = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0);
    }
	
    foreach ($post->tips as $key => $value) {
	if ($value == 0) {
            // found a valid tip number and set it
            $post->tips[$key] = $tip_id;
	    $post->tips_number ++;
            return true;
	}
    }

    return false;
}

/**
 * Delete one tip from market post
 *
 * @param Market $post
 * @return if we can add a tip
 */
function market_delete_tip($post = NULL, $tip_id = 0) {

    if (!$post || $tip_id == 0) {
	return false;
    }

    foreach ($post->tips as $key => $value) {
	if ($value == $tip_id) {
            // Found the requested tip number and delete it
	    // Unlink the tip to this post

            $post->tips[$key] = 0;
	    $post->tips_number --;
            if ($post->tips_number < 0) {
                return false;
            } else {
                return true;
            }

	}
    }
    // Can't found the requested tip to delete.
    return false;
}
