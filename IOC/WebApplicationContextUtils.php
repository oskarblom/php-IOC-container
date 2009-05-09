<?php
/**
 * 
 * @author oskarb
 * Static helper class
 */
class WebApplicationContextUtils {

	const CACHE_FILE = 'app_context_cache';
		
	/**
	 * @param string
	 * @return ApplicationContext
	 */
	public static function getContext($applicationContextConfig) {
		
		if(self::contextUpdated($applicationContextConfig)) {
			$appContext = new WebApplicationContext($applicationContextConfig);
			file_put_contents(self::CACHE_FILE, serialize($appContext));
		} else {
			$appContext = @unserialize(file_get_contents(self::CACHE_FILE));
		} 
		if(!$appContext instanceof ApplicationContext) {
			throw new Exception('unable to get application context');
		}
		return $appContext;
	}
	
	/**
	 * @param string
	 * @return bool
	 */
	private static function contextUpdated($applicationContextConfig) {
		return (@filemtime(self::CACHE_FILE) < @filemtime($applicationContextConfig));
	}
}
?>