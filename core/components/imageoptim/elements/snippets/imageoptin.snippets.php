<?php
// get config
$config = array();
$config['cachePath'] = rtrim($modx->getOption('imageoptim.cachePath', $scriptProperties, '{assets_path}imagecache/'), '/').'/';
$config['cacheUrl'] = rtrim($modx->getOption('imageoptim.cacheUrl', $scriptProperties, '{assets_url}imagecache/'), '/').'/';
$config['imageBaseUrl'] = rtrim($modx->getOption('imageoptim.image_base_url', $scriptProperties, '{assets_url}uploads/{context_key}/images/'), '/').'/';
$config['debug'] = true; //$modx->getOption('debug', $scriptProperties, $modx->getOption('imageoptim.debug', null, false));

// replace placeholders in path & url configs
$config['cachePath'] = str_replace('{context_key}', $modx->context->key, $config['cachePath']);
$config['cacheUrl'] = str_replace('{context_key}', $modx->context->key, $config['cacheUrl']);
$config['imageBaseUrl'] = str_replace('{context_key}', $modx->context->key, $config['imageBaseUrl']);
$config['cachePath'] = str_replace('{base_url}', $modx->getOption('base_url'), $config['cachePath']);
$config['cacheUrl'] = str_replace('{base_url}', $modx->getOption('base_url'), $config['cacheUrl']);
$config['imageBaseUrl'] = str_replace('{base_url}', $modx->getOption('base_url'), $config['imageBaseUrl']);
$config['cachePath'] = str_replace('{base_path}', $modx->getOption('base_path'), $config['cachePath']);
$config['cacheUrl'] = str_replace('{base_path}', $modx->getOption('base_path'), $config['cacheUrl']);
$config['imageBaseUrl'] = str_replace('{base_path}', $modx->getOption('base_path'), $config['imageBaseUrl']);
$config['cachePath'] = str_replace('{assets_path}', $modx->getOption('assets_path'), $config['cachePath']);
$config['cacheUrl'] = str_replace('{assets_path}', $modx->getOption('assets_path'), $config['cacheUrl']);
$config['imageBaseUrl'] = str_replace('{assets_path}', $modx->getOption('assets_path'), $config['imageBaseUrl']);
$config['cachePath'] = str_replace('{assets_url}', $modx->getOption('assets_url'), $config['cachePath']);
$config['cacheUrl'] = str_replace('{assets_url}', $modx->getOption('assets_url'), $config['cacheUrl']);
$config['imageBaseUrl'] = str_replace('{assets_url}', $modx->getOption('assets_url'), $config['imageBaseUrl']);

// get input URL
$imageUrl = $modx->getOption('input', $scriptProperties, '');
if (empty($imageUrl)) return '';
$imageOrgSource = $imageUrl;
// strip domain from url and make sure the url is absolute
$imageUrl = '/'.ltrim(parse_url($imageUrl, PHP_URL_PATH), '/');
// get path pars
$imageUrlParts = pathinfo($imageUrl);

// get API options ('full' is the default with is just doing optimizations without scaling)
$options = $modx->getOption('options', $scriptProperties, 'full');

// generate cache url and path
$cacheFilePath = str_replace($config['imageBaseUrl'], '', $imageUrlParts['dirname']);
$cacheFilename = $imageUrlParts['filename'].'.'.hash('crc32', 'thumb-' . $imageUrl .$options).'.'.$imageUrlParts['extension'];
$cachePath = $config['cachePath'] . trim($cacheFilePath, '/') . '/' . $cacheFilename;
$cacheUrl = $config['cacheUrl'] . trim($cacheFilePath, '/') . '/' . $cacheFilename;

// if file is already optimized, return it
if (file_exists($cachePath)) {
    return $cacheUrl;
}

// make sure cache path exists and is writeable
if (!is_writable(dirname($cachePath))) {
	if (!$modx->cacheManager->writeTree(dirname($cachePath))) {
		$modx->log(modX::LOG_LEVEL_ERROR, '[imageoptim] cache path is not writable: '.dirname($cachePath));
		return $imageOrgSource;
	}
}


// find the absolute path of the file
$realphath = $imageUrl;
if (file_exists($realphath) === false) {
    $realpath = rtrim($modx->getOption('base_path'), '/') . $imageUrl;
    if (file_exists($realpath) === false) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptim] could not find absolute path to input image: '.$realpath);
        return $imageOrgSource;
    }
}


// do the API request
$timeout = $modx->getOption('timeout', $scriptProperties, 10);
$username = $modx->getOption('imageoptim.username', null, false);
if (!$username || empty($username)) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptim] required system setting "imageoptim.username" is empty');
    return $imageOrgSource;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://im2.io/'.$username.'/'.$options);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => curl_file_create($realpath),
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$responseinfo = curl_getinfo($ch);
curl_close ($ch);

// log debug
if ($config['debug']) {
    $debugConst = modX::LOG_LEVEL_ERROR;
} else {
    $debugConst = modX::LOG_LEVEL_DEBUG;
}
// log debug message
$modx->log($debugConst, '[imageoptim] generating cache file for input '.$imageUrl.' ('.$imageOrgSource.') at '. $cachePath .' with URL '. $cacheUrl .'. API response code was '.$responseinfo['http_code'].' after '.$responseinfo['total_time'].'s.');


if ($responseinfo['http_code'] == 200) {
    // save optimized image
    if (@file_put_contents($cachePath, $response) === false) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptim] could not write cache file at '.$cachePath);
        return $imageOrgSource;
    }
    return $cacheUrl;
} else {
    $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptim] API response error: '.$response);
    return $imageOrgSource;
}