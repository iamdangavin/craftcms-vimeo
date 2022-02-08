<?php

namespace iamdangavin\vimeo\services;

use Craft;
use craft\services\Path;

class getVimeo
{
	
	public function get(string $video_id){

		if($video_id === false) {
			return null;
		}

		if(preg_match('|^https?://|', $video_id)) {
			$video_id = explode('/', $video_id);
			$video_id_tmp = array_pop($video_id);
			while(!preg_match('/^\d+$/', $video_id_tmp)) {
				$video_id_tmp = array_pop($video_id);
			}
			if(!$video_id_tmp) {
				return false;
			}
			$video_id = $video_id_tmp;
		}

		$access_token = Craft::$app->plugins->getPlugin('vimeo')->getSettings()->vimeoAPIKey;
		$access_cache = Craft::$app->plugins->getPlugin('vimeo')->getSettings()->vimeoAPICache;
		$access_cache = ($access_cache)?$access_cache:60;
		if(!$access_token){
			return;
		}
		$access_header = 'Authorization: bearer ' . $access_token;

		$opts = array(
			'http'=>array(
				'method' => 'GET',
				'header' => "$access_header\r\nAccept: application/vnd.vimeo.video",
				'follow_location' => 1,
				'ignore_errors' => true
			)
		);

		$context = stream_context_create($opts);
		$return = $this->getTMPVimeo('https://api.vimeo.com/videos/' . $video_id, $access_cache, $context);
		$return = json_decode($return);
		$srcs = array();
		
		// Check for files first
		if(isset($return->files)){
			foreach($return->files as $file) {
				if(isset($file->width)) {
					$file->poster = str_replace('_1280', '', $return->pictures[0]->link);
					$file->fallbackurl = $return->link;
					$file->name = $return->name;
					$srcs[] = $file;
				}
			}
		// Then downloadable files
		}elseif(isset($return->download)) {
			foreach($return->download as $file) {
				if(isset($file->width)) {
					$file->poster = str_replace('_1280', '', $return->pictures[0]->link);
					$file->fallbackurl = $return->link;
					$file->name = $return->name;
					$srcs[] = $file;
				}
			}
		}else{
			return;
		}

		usort($srcs, function($a, $b){
			if($a->width > $b->width){
				return false;
			}else{
				return true;
			}
		});

		return $srcs[0];
	}

	/**
	 * Get a File (API Call) and Cache the Results
	 *
	 * @param		string $url Path to remote API.
	 * @param		int $cachetime Time to cache.
	 * @param		resource $context A stream context resource created with stream_context_create().
	 */
	private function getTMPVimeo($url, $cachetime = 60, $context = false){
	
		$system_temp = Craft::$app->getPath()->getTempPath();
		
		// Get the site's temp folder.
		$cache_file = $system_temp . '/' . md5($_SERVER['HTTP_HOST'] . Craft::$app->getPath()->getTempPath());
		
		if(!file_exists($cache_file)) {
			@mkdir($cache_file, 0777);
		}
		
		// Append a hash of the URL.
		$cache_file = $cache_file . '/' . md5($url) . '.cache';

		// Still cached
		if(
			file_exists($cache_file) &&
			(filemtime($cache_file) > (time() - 60 * $cachetime))
		){
			$results = file_get_contents($cache_file);
			return $results;
		}else{
			$results = file_get_contents($url, false, ($context ? $context : null));
			if($results) {
				$f = @fopen($cache_file, 'w');
				if($f) {
					fwrite($f, $results);
					fclose($f);
					return $results;
				} else {
					return $results;
				}
			}
		}
	}
}