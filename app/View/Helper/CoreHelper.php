<?php
class CoreHelper extends AppHelper {

    /**
     * Genera la cadena de scripts segun los requeridos por el parametro
     * @param array $scripts arreglo de scripts requeridos
     * @return string
     */
    public function script($scripts){
        $site = $this->_View->getVar('site');     
        $pluginsUrl = Configure::read('App.pluginsBaseUrl');     
        $scripts = is_array($scripts) ? $scripts : array($scripts);       
        $scriptsStr = '';
        foreach ($scripts AS $script){            
            //$scriptsStr .= trim($script)!='' ? '<script type="text/javascript" src="'.$site.$script.'.js?'.time().'"></script>' : '';
            $scriptsStr .= trim($script)!='' ? '<script type="text/javascript" src="'.$site.$script.'.js"></script>' : '';
        }
        return $scriptsStr;
    }
    
    /**
     * Genera la cadena de hojas de estilos requeridos segun el parametro
     * @param array $stylesheets array de hojas de estilo requeridos
     * @return string
     */
    public function css($stylesheets, $options = []){
        $site = $this->_View->getVar('site'); 
        $pluginsUrl = Configure::read('App.pluginsBaseUrl');      
        $stylesheets = is_array($stylesheets) ? $stylesheets : array($stylesheets);       
        $stylesheetsStr = '';
        //var_dump(Configure::read('App.pluginsBaseUrl'));
        foreach ($stylesheets AS $stylesheet){
            $stylesheetsStr .= trim($stylesheet)!='' ? '<link href="'.$site.$stylesheet.'.css" type="text/css" rel="stylesheet" media="screen">':'';
        }
        return $stylesheetsStr;
    }

}