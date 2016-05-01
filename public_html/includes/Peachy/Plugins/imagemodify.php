<?php

/*
This file is part of Peachy MediaWiki Bot API

Peachy is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class ImageModify extends Image {

	function __construct( &$wikiClass, $imagename ) {
		if( !function_exists( 'ImageCreateTrueColor' ) ) {
			throw new DependancyError( "GD", "http://us2.php.net/manual/en/book.image.php" );
		}
		
		parent::__construct( $wikiClass, $imagename );
	}
	
	/**
	 * Resize an image
	 * 
	 * @access public
	 * @param int $width Width of resized image. Default null
	 * @param int $height Height of resized image. Default null.
	 * @param bool $reupload Whether or not to automatically upload the image again. Default false
	 * @param string $newname New filename when reuploading. If not null, upload over the old file. Default null.
	 * @param string $text Text to use for the image name
	 * @param string $comment Upload comment. 
	 * @param bool $watch Whether or not to watch the image on uploading
	 * @param bool $ignorewarnings Whether or not to ignore upload warnings
	 * @return void
	 */
	public function resize( $width = null, $height = null, $reupload = false, $newname = null, $text = '', $comment = '', $watch = false, $ignorewarnings = true ) {
		global $pgIP;
		
		if( !is_null( $width ) && !is_null( $height ) ) {	
			$this->download();
			
			$type = substr( strrchr( $this->mime, '/' ), 1 );

			switch ($type) {
				case 'jpeg':
				    $image_create_func = 'ImageCreateFromJPEG';
				    $image_save_func = 'ImageJPEG';
					$new_image_ext = 'jpg';
				    break;
				
				case 'png':
				    $image_create_func = 'ImageCreateFromPNG';
				    $image_save_func = 'ImagePNG';
					$new_image_ext = 'png';
				    break;
				
				case 'bmp':
				    $image_create_func = 'ImageCreateFromBMP';
				    $image_save_func = 'ImageBMP';
					$new_image_ext = 'bmp';
				    break;
				
				case 'gif':
				    $image_create_func = 'ImageCreateFromGIF';
				    $image_save_func = 'ImageGIF';
					$new_image_ext = 'gif';
				    break;
				
				case 'vnd.wap.wbmp':
				    $image_create_func = 'ImageCreateFromWBMP';
				    $image_save_func = 'ImageWBMP';
					$new_image_ext = 'bmp';
				    break;
				
				case 'xbm':
				    $image_create_func = 'ImageCreateFromXBM';
				    $image_save_func = 'ImageXBM';
					$new_image_ext = 'xbm';
				    break;
				
				default:
					$image_create_func = 'ImageCreateFromJPEG';
				    $image_save_func = 'ImageJPEG';
					$new_image_ext = 'jpg';
			}

			$image = imagecreatetruecolor( $width, $height );

			$new_image = $image_create_func( $pgIP . 'Images/' . $this->localname );
			
			$info = getimagesize( $pgIP . 'Images/' . $this->localname );

			imagecopyresampled( $image, $new_image, 0, 0, 0, 0, $width, $height, $info[0], $info[1] );

        	$image_save_func( $image, $pgIP . 'Images/' . $this->localname );

		}
		elseif( !is_null( $width ) ) {
			$this->download( null, $width );
		}
		elseif( !is_null( $height ) ) {
			$this->download( null, $height + 100000, $height );
		}
		else {
			throw new BadEntryError( "NoParams", "No parameters given" );
		}
		
		if( $reupload ) {
			if( !is_null( $newname ) ) {
				$localname = $newname;
			}
			return $this->upload( null, $text, $comment, $watch, $ignorewarnings );
		}
		
	}

}