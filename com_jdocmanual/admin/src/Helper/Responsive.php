<?php

/**
 * @copyright   Copyright (C) 2021 Dimitrios Grammatikogiannis. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Cefjdemos\Component\Jdocmanual\Administrator\Helper\Thumbs;

/**
 * Content responsive images plugin
 */
class Responsive
{
    private $params;
    private $enabled        = false;
    private $enableWEBP     = false;
    private $enableAVIF     = false;
    private $excludeFolders = [];
    private $separator      = '_';
    private $qualityJPG     = 75;
    private $qualityWEBP    = 60;
    private $qualityAVIF    = 40;
    private $scaleUp        = false;
    private $driver         = 'gd';
    private $validSizes     = [576, 768, 992, 1200];
    private $validExt       = ['jpg', 'jpeg', 'png']; // 'webp', 'avif'
    private $breakpoints;

    public function __construct()
    {
        // Change this section to use jdocmanual parameters
        $this->enabled = true;
        $this->enableWEBP = true;
        $this->breakpoints = $this->validSizes;

        /*
        $this->enabled = PluginHelper::isEnabled('content', 'responsive');
        if ($this->enabled) {
          $plugin            = PluginHelper::getPlugin('content', 'responsive');
          $this->params      = new Registry($plugin->params);
          $this->enableWEBP  = (bool) $this->params->get('enableWEBP', 1);
          $this->enableAVIF  = (bool) $this->params->get('enableAVIF', 0);
          $this->qualityJPG  = (int) $this->params->get('qualityJPG', 75);
          $this->qualityWEBP = (int) $this->params->get('qualityWEBP', 60);
          $this->qualityAVIF = (int) $this->params->get('qualityAVIF', 40);
          $this->scaleUp     = (bool) $this->params->get('scaleUp', false);
          $this->separator   = $this->params->get('separator', '_');
          $this->driver      = $this->params->get('preferedDriver', 'gd');
          $excludeFolders    = preg_split('/[\s,]+/', $this->params->get('excludeFolders', ''));
          $sizes             = preg_split('/[\s,]+/', $this->params->get('sizes', ''));

          if (!is_array($sizes) || count($sizes) < 1) $sizes = [320, 768, 1200];

          asort($sizes);
          $this->validSizes = $sizes;
          $this->excludeFolders = array_map(function ($folder) {
        return JPATH_ROOT . '/' . $folder;
          }, $excludeFolders);
          */
    }

  /**
   * Takes an image tag and returns the picture tag
   *
   * @param string  $image        the image tag
   *
   * @return string
   *
   * @throws \Exception
   */
    public function transformImage($image): string
    {
        $matches = [];
        preg_match_all('/src="([^"]+)"/', $image, $matches);
      // We can't handle this
        if (count($matches) === 0 || count($matches[1]) === 0) {
            return $image;
        }

        $src = $matches[1][0];
        if ($src) {
            $paths = $this->getPaths($src);
            $image = preg_replace('(src="(.*?)")', 'src="' . ltrim($paths->path, '/') . '"', $image);
        }

      // Valid root path and not excluded path
        if (
            empty($paths)
            || strpos($paths->pathReal, JPATH_ROOT) !== 0
            || strpos($paths->pathReal, JPATH_ROOT) === false
            || $this->isExcludedFolder(dirname($paths->pathReal))
        ) {
            return $image;
        }

        $pathInfo = pathinfo($paths->path);

        return $this->buildSrcset(
            (object) [
            'dirname'   => $pathInfo['dirname'],
            'filename'  => $pathInfo['filename'],
            'extension' => $pathInfo['extension'],
            'tag'       => $image,
            ],
        );
    }

  /**
   * Build the srcset string
   *
   * @param  object   $image        the image attributes, expects dirname, filename, extension
   *
   * @return string
   *
   * @since  1.0
   */
    private function buildSrcset(object $image): string
    {
      // When called from the web interface with Joomla in a subfolder this
      // function returns the subfolder name. But from the cli $base is empty;
        $base = ComponentHelper::getComponent('com_jdocmanual')->getParams()->get('installation_subfolder', '');

        $srcSets = $this->createImages(str_replace('%20', ' ', $image->dirname), $image->filename, $image->extension);
        if (null === $srcSets || $srcSets === false) {
            $image->tag = str_replace('jdmimages/', $base . '/' . 'jdmimages/', $image->tag);
            return $image->tag;
        }

        $type   = in_array(mb_strtolower($image->extension), ['jpg', 'jpeg']) ? 'jpeg' : mb_strtolower($image->extension);
        $output = '<picture class="responsive-image">';
        $sizesAttr = isset($srcSets->base->sizes) && count($srcSets->base->sizes) ? ' sizes="' . implode(', ', array_reverse($srcSets->base->sizes)) . '"' : '';

        if (isset($srcSets->avif) && count($srcSets->avif->srcset) > 0) {
            $srcSetAvif = $this->getSrcSets($srcSets->avif->srcset);
            if ($srcSetAvif !== '') {
                $output .= '<source type="image/avif"  srcset="' . $srcSetAvif . '" ' . $sizesAttr . '>';
            }
        }

        if (isset($srcSets->webp) && count($srcSets->webp->srcset) > 0) {
            $srcSetWebp = $this->getSrcSets($srcSets->webp->srcset);
            if ($srcSetWebp !== '') {
                $output .= '<source type="image/webp" srcset="' . $srcSetWebp . '"' . $sizesAttr . '>';
            }
        }

        $srcSetOrig = $this->getSrcSets($srcSets->base->srcset);
        if ($srcSetOrig !== '') {
            $output .= '<source type="image/' . $type . '" srcset="' . $srcSetOrig . '"' . $sizesAttr . '>';
        }
        $output = str_replace('jdmimages/', $base . '/' . 'jdmimages/', $output);

        $heightMatches = [];
        $widthMatches  = [];
        $loadingMatches = [];
        $decodingMatches = [];
        preg_match_all('/height="([^"]+)"/', $image->tag, $heightMatches);
        preg_match_all('/width="([^"]+)"/', $image->tag, $widthMatches);
        preg_match_all('/loading="([^"]+)"/', $image->tag, $loadingMatches);
        preg_match_all('/decoding="([^"]+)"/', $image->tag, $decodingMatches);

        if (count($loadingMatches) === 0 || count($loadingMatches[1]) === 0) {
            $image->tag = str_replace('<img ', '<img loading="lazy" ', $image->tag);
        }
        if (count($decodingMatches) === 0 || count($decodingMatches[1]) === 0) {
            $image->tag = str_replace('<img ', '<img decoding="async" ', $image->tag);
        }
        if (count($heightMatches) === 0 || count($heightMatches[1]) === 0) {
            $image->tag = str_replace('<img ', '<img height="' . $srcSets->base->height . '" ', $image->tag);
        } else {
            $image->tag = preg_replace('(height="(.*?)")', 'height="' . $srcSets->base->height . '"', $image->tag);
        }
        if (count($widthMatches) === 0 || count($widthMatches[1]) === 0) {
            $image->tag = str_replace('<img ', '<img width="' . $srcSets->base->width . '" ', $image->tag);
        } else {
            $image->tag = preg_replace('(width="(.*?)")', 'width="' . $srcSets->base->width . '"', $image->tag);
        }

        $image->tag = preg_replace('(src="(.*?)")', 'src="' . $base . $image->dirname . '/' . $image->filename . '.' . $image->extension . '"', $image->tag);

        // Create the fallback img
        return  $output . $image->tag . '</picture>';
    }

  /**
   * Create the thumbs
   *
   * @param string   $dirname       the folder name
   * @param string   $filename      the file name
   * @param string   $extension     the file extension
   *
   * @return object | void          image data
   *
   * @since  1.0
   */
    private function createImages($dirname, $filename, $extension)
    {
      // remove the first slash
        $dirname = ltrim($dirname, '/');

      // Getting the image info
        $info = @getimagesize(JPATH_ROOT . '/' . $dirname . '/' . $filename . '.' . $extension);
        $hash = hash_file('md5', JPATH_ROOT . '/' . $dirname . '/' . $filename . '.' . $extension);

        if (empty($info)) {
            return;
        }

        $imageWidth = $info[0];
        $imageHeight = $info[1];

      // Skip if the width is less or equal to the required
        if ($imageWidth <= (int) $this->validSizes[0]) {
            return;
        }

      // Check if we support the given image
        if (!in_array($info['mime'], ['image/jpeg', 'image/webp', 'image/png', 'image/avif'])) {
            return;
        }

        $sourceType = str_replace('image/', '', $info['mime']);
        $channels = isset($info['channels']) ? $info['channels'] : 4;

        if (!isset($info['bits'])) {
            $info['bits'] = 16;
        }

        $imageBits = ($info['bits'] / 8) * $channels;

      // Do some memory checking
        if (
            !self::checkMemoryLimit(['width' => $imageWidth,
            'height' => $imageHeight,
            'bits' => $imageBits], $dirname . '/' . $filename . '.' . $extension)
        ) {
            return;
        }

        $srcSets = (object) [
            'base' => (object) [
            'srcset' => [],
            'sizes' => [],
            'width' => $info[0],
            'height' => $info[1],
            'version' => $hash,
            ]
        ];
        // (max-width: 300px) 100vw, (max-width: 600px) 50vw, (max-width: 900px) 33vw, 900px 320, 768, 1200
        // array_push($srcSets->base->sizes, '(max-width: 320px) 100vw, (max-width: 768px) 50vw, (max-width: 1200px) 33vw, 1200px');
        array_push($srcSets->base->sizes);
        $img = (object) [
            'dirname'   => $dirname,
            'filename'  => $filename,
            'extension' => $extension,
            'width'     => $imageWidth,
            'height'    => $imageHeight,
            'type'      => $sourceType,
        ];
        $options = (object) [
            'destination' => 'media/cached-resp-images/',
            'enableWEBP'  => $this->enableWEBP,
            'enableAVIF'  => $this->enableAVIF,
            'qualityJPG'  => $this->qualityJPG,
            'qualityWEBP' => $this->qualityWEBP,
            'qualityAVIF' => $this->qualityAVIF,
            'scaleUp'     => $this->scaleUp,
            'separator'   => trim($this->separator),
            'validSizes'  => $this->validSizes,
        ];

        try {
            $thumbs = new Thumbs($this->driver);
            return $thumbs->create($img, $options, $srcSets);
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
        }
    }

  /**
   * Check memory boundaries
   *
   * @param object  $properties   the Image properties object
   * @param string  $imagePath    the image path
   *
   * @return bool
   *
   * @since  3.0.3
   *
   * @author  Niels Nuebel: https://github.com/nielsnuebel
   */
    private static function checkMemoryLimit($properties, $imagePath): bool
    {
        $memorycheck  = ($properties['width'] * $properties['height'] * $properties['bits']);
        // $memorycheck_text = $memorycheck / (1024 * 1024);
        $phpMemory    = ini_get('memory_limit');
        $memory_limit = is_numeric($phpMemory) ? $phpMemory : self::toByteSize($phpMemory);

        if (isset($memory_limit) && $memorycheck > $memory_limit) {
            Factory::getApplication()->enqueueMessage('Image too big to be processed', 'error'); //, $imagePath, $memorycheck_text, $memory_limit

            return false;
        }

        return true;
    }

  /**
   * Helper, returns the srcsets from the object for the given breakpoints
   *
   * @param object $obj
   */
    private function getSrcSets($obj): string
    {
        $retArr = [];
        foreach ($obj as $key => $val) {
            if (in_array($key, $this->breakpoints)) {
                $retArr[] = $val;
            }
        }

        if (count($retArr) > 0) {
            return implode(', ', array_reverse($retArr));
        }

        return '';
    }

    private function getPaths($path)
    {
        $path = MediaHelper::getCleanMediaFieldValue(str_replace(Uri::base(), '', $path));
        $path = (substr($path, 0, 1) === '/' ? $path : '/' . $path);

        return (object) [
            'path' => str_replace('%20', ' ', $path),
            'pathReal' => realpath(JPATH_ROOT . str_replace('%20', ' ', $path)),
        ];
    }

    private function isExcludedFolder($path)
    {
        $isExcluded = false;

        foreach ($this->excludeFolders as $folder) {
            if (substr($path, 0, strlen($folder)) === $folder) {
                $isExcluded = true;
                break;
            }
        }

        return $isExcluded;
    }

    private static function toByteSize($formated)
    {
        $formated = strtolower(trim($formated));
        $unit = substr($formated, -1, 1);
        $formated = substr($formated, 0, -1);

        if ($unit == 'g') {
            $formated *= 1024 * 1024 * 1024;
        } elseif ($unit == 'm') {
            $formated *= 1024 * 1024;
        } elseif ($unit == 'k') {
            $formated *= 1024;
        } else {
            return false;
        }
        return $formated;
    }
}
