<?php /*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file index.php
 *     View layer for action index of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */ ?>
<style>
    #championship table tbody tr td{
        -webkit-transition: all 0.75s linear;
        -moz-transition: all 0.75s linear;
        transition: all 0.75s linear;
    }

    #championship table tbody tr td b{
        font-weight: bold !important;
        font-family: "sansbook";
    }

    #championship table tbody tr:hover td{
        background-color: #EEE;
    }

    .tab-permissions-market{
    }
    .tab-permissions-market > h1 {
        font-size: 19px;
        font-family: "sansbook";
        margin: 0 0 20px;
        padding: 0;
    }
    .tab-permissions-market > h1 > span{
    }
</style>

<?php
$urlRefreshChampionship = $this->Html->url(array('controller' => 'Advanced', 'action' => 'refreshSalespersonTable'));
$urlSavePermissionsRoleUrl = $this->Html->url(array('controller' => 'Advanced', 'action' => 'savePermissionsRole'));
?>
<div class="title-page advanced-section">
    <?php echo __('Admin settings', true); ?>
</div>
<div class="full-page">
    <div class="space"></div>
    <div class="space"></div>
    <div class="page-menu">
        <ul id="conti_menu">
            <li><?php echo $this->Html->link(__('Seguridad', true), '#', array('rel' => 'security', 'class' => $active_tab == 'security' ? 'active' : '')); ?></li>
            <!--<li><?php echo $this->Html->link(__('Estadisticas', true), '#', array('rel' => 'statistics', 'class' => $active_tab == 'statistics' ? 'active' : '')); ?></li>-->
            <li><?php echo $this->Html->link(__('Activity', true), '#', array('rel' => 'history', 'class' => $active_tab == 'history' ? 'active' : '')); ?></li>
            <li><?php echo $this->Html->link(__('Permissions', true), '#', array('rel' => 'permissions', 'class' => $active_tab == 'permissions' ? 'active' : '')); ?></li>
        </ul>
    </div>
    <div class="wrapper-content">
        <div id="distributors" class="<?php echo $active_tab == 'distributor' ? '':'hidden';?>">
            <?php $this->Content->printCompanies($dist_companies, Group::DISTRIBUTOR); ?>
        </div>
        <div id="clients" class="<?php echo $active_tab == 'client' ? '':'hidden';?>">
            <?php $this->Content->printCompanies($client_companies, Group::CLIENT); ?>
        </div>

        <div id="security" class="<?php echo $active_tab == 'security' ? '' : 'hidden'; ?>">
            <?php $this->Content->printLockingLog($locking_log); ?>
        </div>
        <div id="statistics" class="<?php echo $active_tab == 'statistics' ? '' : 'hidden'; ?>">
            <?php echo $this->Html->script("https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1','packages':['corechart']}]}"); ?>            
            <form class="hidden" id="filter_date_form" method="post" action="<?php echo $this->Html->url(array('controller'=>'Advanced','action'=>'index')); ?>">
                <input type="hidden" name="query_user"/>
                <input type="hidden" name="active_tab"/>
                <input type="hidden" name="ini_date"/>
                <input type="hidden" name="end_date"/>
            </form>
            <div id="panel_data">
                <div class="highlighted">
                    <h1><?php echo __('Transportadores totales', true); ?></h1>
                    <span><?php echo $total_bandas; ?></span>
                </div>
                <div>
                    <h1><?php echo __('Usuarios registrados', true); ?></h1>
                    <span><?php echo $total_usuarios; ?></span>
                </div>
                <div>
                    <h1><?php echo __('Clientes', true); ?></h1>
                    <span><?php echo $total_clientes; ?></span>
                </div>
                <div>
                    <h1><?php echo __('Distribuidores', true); ?></h1>
                    <span><?php echo $total_distribuidores; ?></span>
                </div>
                <div>
                    <h1><?php echo __('Visitas promedio', true); ?></h1>
                    <span><?php echo $visitas_totales; ?></span>
                </div>
                <div class="last">
                    <h1><?php echo __('Tiempo promedio/vista', true); ?></h1>
                    <span><?php echo $tiempo_promedio; ?> m</span>
                </div>
            </div>
            <table id="graphic_data">
                <tbody>
                    <tr>
                        <td><div id="visits_graphic"></div></td>
                        <td><div id="country_visits_graphic"></div></td>
                    </tr>
                    <tr>
                        <td><div id="browser_visits_graphic"></div></td>
                        <td><div id="os_visits_graphic"></div></td>
                    </tr>
                    <tr>
                        <td colspan="2"><div class="space"></div></td>
                    </tr>
                    <tr>
                        <td>
                            <table id="activity_companies">
                                <tr>
                                    <th><?php echo __('Empresas mas activas', true); ?></th>
                                    <th><?php echo __('Empresas menos activas', true); ?></th>
                                </tr>
                                <tr>
                                    <td>
                                        <?php
                                        if (!empty($actividad_empresas)) {
                                            for ($i = 0; $i < 5 && count($actividad_empresas) > 0; $i++) {
                                                $activity = array_shift($actividad_empresas);
                                                if ($activity['ActivityCompanies']['activity'] <= 0) {
                                                    $i = 5;
                                                    array_unshift($actividad_empresas, $activity);
                                                } else {
                                                    echo '<div class="green-color">' . $activity['ActivityCompanies']['company'] . '</div>';
                                                }
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (!empty($actividad_empresas)) {
                                            for ($i = 0; $i < 5 && count($actividad_empresas) > 0; $i++) {
                                                $activity = array_shift($actividad_empresas);
                                                echo '<div class="red-color">' . $activity['ActivityCompanies']['company'] . '</div>';
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td><div id="section_visits_graphic"></div></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="history" class="<?php echo $active_tab == 'history' ? '' : 'hidden'; ?>">
            <?php $this->Content->printBrowsingLog($browsing_log); ?>
        </div>

        <div id="permissions" class="<?php echo $active_tab == 'permissions' ? '' : 'hidden'; ?>">
            <?php foreach ($groupPermissionsMarket AS $market_id => $groupPermissions): ?>
                <div class="tab-permissions-market <?php if($credentials['assoc_market']!=$market_id): ?> hidden <?php endif; ?>" data-mktid="<?php echo $market_id; ?>">
                    <!--<h1><?php echo __("Selected market",true); ?>: <span><?php echo $markets[$credentials['assoc_market']]; ?></span></h1>-->
                <?php
               $permission_data = '';
               $blackListElements = [IElement::Is_MinuteMan, IElement::Is_WelcomeMsg, IElement::Is_ContactMsg,
                   IElement::Is_Profile, IElement::Is_Calculator, IElement::Is_ContiUniversity,
                   IElement::Is_Notification, IElement::Is_HelpSection, IElement::Is_TermsSection, IElement::Is_QRCode, IElement::Is_Tutorial];

                foreach ($groupPermissions AS $groupPermission){
                    $permissions = $groupPermission['PermissionsForGroup'];
                    $group = $groupPermission['IGroup'];

                    $permissions_for_group = '';
                    if(!empty($permissions)){
                        $permissions_for_group = '<div class="clients-distributor permissions-table">';
                        $permissions_for_group .= '<table>';
                        $permissions_for_group .= '<thead><tr>';
                        $permissions_for_group .= '<th>Element</th>';
                        $permissions_for_group .= '<th>Add</th>';
                        $permissions_for_group .= '<th>Edit</th>';
                        $permissions_for_group .= '<th>Delete</th>';
                        $permissions_for_group .= '<th>View</th>';
                        $permissions_for_group .= '<th>Download</th>';
                        $permissions_for_group .= '</tr></thead>';
                        $permissions_for_group .= '<tbody>';
                        foreach ($permissions AS $permission){

                            $element = $permission['Element'];
                            $permission_defined = $permission['permission'];
                            $activePermissons = [];
                            $classChecks = ['','','','',''];
                            switch ($permission['id']){
                                case 5: //permission report for rol customer
                                    $classChecks = ['','hidden','hidden','',''];
                                 break;
                            }

                            //for permission element
                            switch ($permission['element_id']){
                                case 27: //element notifications (can't edit, download)
                                    $classChecks = ['','hidden','','','hidden'];
                                break;
                                case 12: //element files (can't view->download is view)
                                    $classChecks = ['','','','hidden',''];
                                    break;
                                case 13: //element ultrasonic data (can't view, download)
                                    $classChecks = ['','','','hidden','hidden'];
                                    break;
                                case 1:case 16:case 21://element conveyors, profile own (can't download)
                                    $classChecks = ['','','','','hidden'];
                                break;
                                case 10: //element welcome msg (can't view, delete, download)
                                    $classChecks = ['','','hidden','hidden','hidden'];
                                    break;
                                case 22: case 39://element scheduled notification, calculator (can't add, edit, delete, download)
                                    $classChecks = ['hidden','hidden','hidden','','hidden'];
                                    break;
                                case IElement::Is_Tutorial:
                                case 23:case 24:case 25:case 26:case 28:case 29:case 33:case 34:case 35: //element minute man and contiuniversity, users section, advanced section, help, terms, recommended belt, lifetime (can't add, edit, delete, download)
                                    $classChecks = ['hidden','hidden','hidden','','hidden'];
                                    break;
                                /*case 37: //element technical data (just view, download)
                                    $classChecks = ['hidden','hidden','hidden','',''];
                                    break;*/
                                case 19:case 20:case 30:case 31:case 32:case 38:case IElement::Is_TechnicalData: //report per conveyor,list reports per customer, qr code, ultrasonic report, custom report, smart report (just can download)
                                    $classChecks = ['hidden','hidden','hidden','hidden',''];
                                    break;
                            }

                            $activePermissions['create'] = $permission_defined[0]=='1' ? 'checked' : '';//add
                            $activePermissions['update'] = $permission_defined[1]=='1' ? 'checked' : '';//edit
                            $activePermissions['delete'] = $permission_defined[2]=='1' ? 'checked' : '';
                            $activePermissions['read'] = $permission_defined[3]=='1' ? 'checked' : ''; //view
                            $activePermissions['download'] = $permission_defined[4]=='1' ? 'checked' : '';


                            if(!in_array($permission['element_id'],$blackListElements))    {
                                $permissions_for_group .= '<tr class="permission-section-row" data-sectionid="'.$permission['id'].'" data-section="'.$element['name'].'" data-version="'.$permission['version'].'" data-market="'.$permission['market_id'].'">';
                                $permissions_for_group .= '<td>'.$element['name'].'</td>';
                                $permissions_for_group .= '<td><input class="'.$classChecks[0].'" type="checkbox" '.$activePermissions['create'].'/></td>';
                                $permissions_for_group .= '<td><input class="'.$classChecks[1].'" type="checkbox" '.$activePermissions['update'].'/></td>';
                                $permissions_for_group .= '<td><input class="'.$classChecks[2].'" type="checkbox" '.$activePermissions['delete'].'/></td>';
                                $permissions_for_group .= '<td><input class="'.$classChecks[3].'" type="checkbox" '.$activePermissions['read'].'/></td>';
                                $permissions_for_group .= '<td><input class="'.$classChecks[4].'" type="checkbox" '.$activePermissions['download'].'/></td>';
                                $permissions_for_group .= '</tr>';
                            }

                        }
                        $permissions_for_group .= '</tbody>';
                        $permissions_for_group .= '</table>';
                        $permissions_for_group .= '</div>';
                    }

                    //<nav class="action-bar-accord" '.$tutorial.'><ul class="action-list">' . $action_btns . '</ul></nav>
                    $permission_data .= '<div class = "accordionButton row-data clients-company-link permissions-row" data-group="' . $group['id'] . '"><nav class="action-bar-accord"><ul class="action-list hidden"><li class="save save-role-link" title="Save" rel="'.$urlSavePermissionsRoleUrl.'"></li></ul></nav><div class="company-name-accord">' . utf8_encode($group['description']) .'</div>'.'</div>' . $permissions_for_group;

                }
                echo $permission_data;
                ?>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>
