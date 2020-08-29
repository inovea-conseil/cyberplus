<?php
/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr>
 *                                                http://www.mikael-carlavan.fr
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 		\defgroup   modFabrication     Module Fabrication
 *      \file       htdocs/core/modules/modFabrication.class.php
 *      \ingroup    modFabrication
 *      \brief      Description and activation file for module modFabrication
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 * 		\class      modCyberPlus
 *      \brief      Description and activation class for module modCyberPlus
 */
class modCyberPlus extends DolibarrModules
{
	/**
	 *   \brief      Constructor. Define names, constants, directories, boxes, permissions
	 *   \param      DB      Database handler
	 */
	function __construct($db)
	{
        global $langs, $conf;

        $this->db = $db;
		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 170000;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'cyberplus';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "financial";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = 'cyberplus';
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module de paiement par carte de crédit avec la solution de paiement CyberPlus";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '2.0.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'cyberplus@cyberplus';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
		//							'login' => 0,                                    	// Set this to 1 if module has its own login method directory (core/login)
		//							'substitutions' => 0,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
		//							'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
		//							'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (core/theme)
		//                        	'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
		//							'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
		//							'models' => 0,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
		//							'css' => array('/mymodule/css/mymodule.css.php'),	// Set this to relative path of css file if module has its own css file
	 	//							'js' => array('/mymodule/js/mymodule.js'),          // Set this to relative path of js file if module must load a js on all pages
		//							'hooks' => array('hookcontext1','hookcontext2')  	// Set here all hooks context managed by module
		//							'workflow' => array('WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2'=>array('enabled'=>'! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)', 'picto'=>'yourpicto@mymodule')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array(
			'triggers' => 1,
			'models' => 1,
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();
		$r=0;

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array('config.php@cyberplus');

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->conflictwith = array('modPaypal', 'modPaybox');	
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,2);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("cyberplus@cyberplus");

		// Constants
		$this->const = array(
			0 => array('CYBERPLUS_URL_SERVER_TEST','chaine','https://paiement.systempay.fr/vads-payment/','',0),
			1 => array('CYBERPLUS_URL_SERVER','chaine','https://paiement.systempay.fr/vads-payment/','', 0),
		);

        $this->tabs = array();
        // Dictionnaries
        $this->dictionnaries = array();

        // Boxes
		// Add here list of php file(s) stored in includes/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes


		// Permissions
		$this->rights = array();		// Permission array used by this module
        $this->rights[$r][0] = 170001; 				// Permission id (must not be already used)
        $this->rights[$r][1] = 'Classer les factures payées';	 // Permission label
        $this->rights[$r][3] = 0; 					 // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'invoice';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = 'update';				 // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++; 
        
        $this->rights[$r][0] = 170002; 				// Permission id (must not be already used)
        $this->rights[$r][1] = 'Clôturer les commandes payées';	 // Permission label
        $this->rights[$r][3] = 0; 					 // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'order';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = 'close';				 // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++;
        
        $this->rights[$r][0] = 170003; 				// Permission id (must not be already used)
        $this->rights[$r][1] = 'Convertir les commandes payées en factures';	 // Permission label
        $this->rights[$r][3] = 0; 					 // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'order';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = 'convert';				 // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++;
        
        $this->rights[$r][0] = 170004; 				// Permission id (must not be already used)
        $this->rights[$r][1] = 'Créer des écritures correspondant aux paiements';	 // Permission label
        $this->rights[$r][3] = 0; 					 // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'payments';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = 'set';				 // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++;
        
        $this->rights[$r][0] = 170005; 				// Permission id (must not be already used)
        $this->rights[$r][1] = 'Emettre un email de confirmation de paiement';	 // Permission label
        $this->rights[$r][3] = 0; 					 // Permission by default for new user (0/1)
        $this->rights[$r][4] = 'email';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $this->rights[$r][5] = 'send';				 // In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
        $r++;
	
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function init($options='')
	{
	   global $conf;

	   $sql = array();

		$result = $this->load_tables();

		return $this->_init($sql, $options);
	}


	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted.
	 *      @return     int             1 if OK, 0 if KO
	 */
	function remove($options='')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}


	/**
	 *		\brief		Create tables, keys and data required by module
	 * 					Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 					and create data commands must be stored in directory /mymodule/sql/
	 *					This function is called by this->init.
	 * 		\return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/cyberplus/sql/');
	}
}

?>
