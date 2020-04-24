<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
 
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
Router::connect('/', array('controller' => 'index', 'action' => 'index' ));

//Router::connect('/login', array('controller' => 'Access', 'action' => 'login'));
Router::connect('/logout', array('controller' => 'Access', 'action' => 'logout'));
Router::connect('/locatedevice', array('controller' => 'Access', 'action' => 'ax_set_position'));
/**
* ...and connect the rest of 'Pages' controller's URLs.
*/
//	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

Router::connect('/Users', array('controller' => 'Users', 'action' => 'all' ));

Router::connect('/Users/customers', array('controller' => 'Users', 'action' => 'clients' ));
Router::connect('/Users/Customers', array('controller' => 'Users', 'action' => 'Clients' ));

Router::connect('/Advanced/dashboard', array('controller' => 'Advanced', 'action' => 'index' ));
Router::connect('/Conveyors', array('controller' => 'Conveyors', 'action' => 'dashboard' ));
Router::connect('/Conveyors/view/*', array('controller' => 'Conveyors', 'action' => 'view' ));
Router::connect('/Conveyors/gauge/*', array('controller' => 'Conveyors', 'action' => 'getGaugeChart' ));
Router::connect('/General', array('controller' => 'General', 'action' => 'terms' ));
Router::connect('/Premium', array('controller' => 'Premium', 'action' => 'index' ));
Router::connect('/Reports', array('controller' => 'Reports', 'action' => 'custom' ));

Router::connect('/Savings', array('controller' => 'Savings', 'action' => 'main' ));

Router::connect('/History', array('controller' => 'History', 'action' => 'view' ));

Router::connect('/help', array('controller' => 'General', 'action' => 'help'));
Router::connect('/terms', array('controller' => 'General', 'action' => 'terms'));
Router::connect('/privacy/*', array('controller' => 'General', 'action' => 'privacy'));


Router::connect('/shareCompany/*', array('controller' => 'Companies', 'action' => 'shareCompanyWithSalesperson' ));
Router::connect('/setSalespersonToCompany/*', array('controller' => 'Companies', 'action' => 'setSalespersonForCompanies' ));

Router::connect('/conveyor/inspections/*', array('controller' => 'Conveyors', 'action' => 'viewInspections' ));
Router::connect('/inspection/*', array('controller' => 'Conveyors', 'action' => 'inspectionData' ));

Router::connect('/customer/bucket/*', array('controller' => 'Companies', 'action' => 'clientBucket' ));

Router::connect('/monitoringsystem/*', array('controller' => 'Premium', 'action' => 'monitoringSystem'));
Router::connect('/monitoring/company/*', array('controller' => 'Premium', 'action' => 'viewMonitoringCompany'));



Router::connect('/settings', array('controller' => 'Users', 'action' => 'all' ));

// route to customers
Router::connect('/customers', array('controller' => 'Users', 'action' => 'clients' ));

// route to all buoy system of all customers
Router::connect('/buoys/dashboard', array('controller' => 'BuoySystems', 'action' => 'dashboard' ));
// route to single buoy system of one client
// Router::connect('/buoys/dashboard/*', array('controller' => 'Companies', 'action' => 'clientBucket' ));
Router::connect('/customer/buoys/*', array('controller' => 'Companies', 'action' => 'view' ));
Router::connect('/buoy/data/*', array('controller' => 'BuoySystems', 'action' => 'viewBuoyFolder' ));
Router::connect('/hose/*', array('controller' => 'Conveyors', 'action' => 'view' ));

/** API REQUEST **/

// Get BS for some client or save one BS Metadata
Router::connect(
	'/clients/:some_client/buoysystems',
	['controller' => 'bs', 'action' => 'clientBuoySystems'], 
	['pass' => ['some_client']]
);

// Get one BS for some client or update one bs meta
Router::connect(
	'/clients/:some_client/buoysystems/:some_buoy', 
	['controller' => 'bs', 'action' => 'clientBuoySystem'],
	['pass' => ['some_client','some_buoy']]
);

// Get Asset for some buoys or save one asset meta
Router::connect(
	'/buoysystems/:some_buoy/assets',
	['controller' => 'asset', 'action' => 'buoySystemAssets'], 
	['pass' => ['some_buoy']]
);

// Get one BS for some client or update one bs meta
Router::connect(
	'/buoysystems/:some_buoy/assets/:some_asset', 
	['controller' => 'asset', 'action' => 'buoySystemAsset'],
	['pass' => ['some_buoy','some_asset']]
);

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
