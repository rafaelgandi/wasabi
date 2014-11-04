<?php
/* 
	Use to autoload class using the modern php standards. Using Symfony Class loader
	component.
	See: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
	See: http://widthauto.com/autoload-classes-wordpress-plugin/
	See: http://www.sitepoint.com/autoloading-and-the-psr-0-standard/
	See: https://github.com/symfony/ClassLoader
	See: http://zalas.eu/autoloading-classes-in-any-php-project-with-symfony2-classloader-component/
 */
require_once vendors_dir()."/Symfony/Component/ClassLoader/UniversalClassLoader.php";
use Symfony\Component\ClassLoader\UniversalClassLoader; 
$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
	// Add additional namespaces here //
	'Pixie' => vendors_dir(), // See: https://github.com/usmanhalalit/pixie
	'Viocon' => vendors_dir(), // See: https://github.com/usmanhalalit/viocon
	'CacheCache' => vendors_dir(), // See: http://maximebf.github.io/CacheCache/
	'App' => app_dir(),
	get_config('app_namespace') => app_dir()
));
$loader->register();

 
 
 
 
 
 
 