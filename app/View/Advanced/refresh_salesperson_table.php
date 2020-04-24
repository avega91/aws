<table>
    <thead>
    <tr>
        <th>Name</th>
        <th>New customer</th>
        <th>New conveyor</th>
        <th>Populate technical data</th>
        <th>Ultrasonic gauge</th>
        <th>Each picture or PDF file</th>
        <th>Savings report (stand-by)</th>
        <th>Savings report (approved)</th>
        <th>Hose life history</th>
        <th>Total points</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(!empty($salespersonsApp)):
        $scoreCard = '';
        foreach ($salespersonsApp AS $salespersonApp){
            $statisticUser = $salespersonApp['Statistics'];
            $salesperson = $salespersonApp['UsuariosEmpresa'];

            $keysAddCustomer = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_CUSTOMER);
            $addCustomerCoincidences = array_map(function($k) use ($statisticUser){ return $statisticUser[$k];}, $keysAddCustomer);

            $keysAddConveyor = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_CONVEYOR);
            $addConveyorCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysAddConveyor);

            $keysFieldsConveyor = array_keys(array_column($statisticUser, 'section'), Statistic::POPULATE_TECHNICAL_DATA);
            $fieldsConveyorCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysFieldsConveyor);

            $sumPopulateFields = 0;
            if(!empty($fieldsConveyorCoincidences)){
                $sumPopulateFields = array_sum(array_column($fieldsConveyorCoincidences, 'applied_changes'));
            }

            $keysAddReading = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_READING_ULTRA);
            $addReadingCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysAddReading);

            $keysAddItemConveyor = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_ITEM_CONVEYOR);
            $addItemConveyorCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysAddItemConveyor);

            $keysAddHistory = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_BELT_HISTORY);
            $addHistoryCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysAddHistory);

            $keysSavingsStandby = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_SAVING_STANDBY);
            $savingsStandbyCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysSavingsStandby);

            $keysSavingsApproved = array_keys(array_column($statisticUser, 'section'), Statistic::NEW_SAVING_APPROVED);
            $savingsApprovedCoincidences = array_map(function($k) use ($statisticUser){return $statisticUser[$k];}, $keysSavingsApproved);

            //Tim ->408, update points 05/01/2018, por cliente duplicado White Buff y clientes no registrados (4), 19 bandas duplicadas
            //this is dif bewtween calcs queries from may21,2017 and current points at sept21,2017 on web app
            $retroPoints = [
                594 => ['countFieldsPopulated'=>374,'countCustomer' => 1, 'countConveyor'=>0, 'countReadings'=>1, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                848 => ['countFieldsPopulated'=>32,'countCustomer' => 1, 'countConveyor'=>1, 'countReadings'=>1, 'countItems'=>1, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                390 => ['countFieldsPopulated'=>0,'countCustomer' => 1, 'countConveyor'=>1, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                499 => ['countFieldsPopulated'=>93,'countCustomer' => 6, 'countConveyor'=>4, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                587 => ['countFieldsPopulated'=>60,'countCustomer' => 1, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                413 => ['countFieldsPopulated'=>0,'countCustomer' => 1, 'countConveyor'=>1, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                407 => ['countFieldsPopulated'=>0,'countCustomer' => 0, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                402 => ['countFieldsPopulated'=>269,'countCustomer' => 0, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>41, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                436 => ['countFieldsPopulated'=>26,'countCustomer' => 1, 'countConveyor'=>1, 'countReadings'=>0, 'countItems'=>1, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                392 => ['countFieldsPopulated'=>61,'countCustomer' => 1, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                491 => ['countFieldsPopulated'=>50,'countCustomer' => 2, 'countConveyor'=>8, 'countReadings'=>1, 'countItems'=>15, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                498 => ['countFieldsPopulated'=>154,'countCustomer' => 7, 'countConveyor'=>8, 'countReadings'=>0, 'countItems'=>8, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                578 => ['countFieldsPopulated'=>0,'countCustomer' => 1, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                418 => ['countFieldsPopulated'=>0,'countCustomer' => 1, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                411 => ['countFieldsPopulated'=>136,'countCustomer' => 2, 'countConveyor'=>9, 'countReadings'=>8, 'countItems'=>1, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                590 => ['countFieldsPopulated'=>7,'countCustomer' => 1, 'countConveyor'=>2, 'countReadings'=>1, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                497 => ['countFieldsPopulated'=>2214,'countCustomer' => 7, 'countConveyor'=>52, 'countReadings'=>20, 'countItems'=>150, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                501 => ['countFieldsPopulated'=>132,'countCustomer' => 4, 'countConveyor'=>3, 'countReadings'=>1, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                431 => ['countFieldsPopulated'=>695,'countCustomer' => 8, 'countConveyor'=>29, 'countReadings'=>1, 'countItems'=>7, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                551 => ['countFieldsPopulated'=>124,'countCustomer' => 1, 'countConveyor'=>5, 'countReadings'=>6, 'countItems'=>5, 'countSavingsStandBy'=>1, 'countSavingsApproved'=>0, 'countHistory'=>0],
                430 => ['countFieldsPopulated'=>224,'countCustomer' => 1, 'countConveyor'=>29, 'countReadings'=>0, 'countItems'=>115, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                589 => ['countFieldsPopulated'=>168,'countCustomer' => 1, 'countConveyor'=>0, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                408 => ['countFieldsPopulated'=>825,'countCustomer' => 6, 'countConveyor'=>20, 'countReadings'=>0, 'countItems'=>0, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
                588 => ['countFieldsPopulated'=>34,'countCustomer' => 1, 'countConveyor'=>1, 'countReadings'=>1, 'countItems'=>2, 'countSavingsStandBy'=>0, 'countSavingsApproved'=>0, 'countHistory'=>0],
            ];

            $totCoincidences = count($addCustomerCoincidences);
            $pointsCustomer = $totCoincidences;
            $pointsCustomer = array_key_exists($salesperson['id'], $retroPoints) ? $pointsCustomer + $retroPoints[$salesperson['id']]['countCustomer'] : $pointsCustomer;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countCustomer']){
                    $difPoints = $retroPoints[$salesperson['id']]['countCustomer'] - $totCoincidences;
                    $pointsCustomer = $totCoincidences + $difPoints;
                }
            }*/
            $pointsCustomer = $pointsCustomer * 10;


            $totCoincidences = count($addConveyorCoincidences);
            $pointsConveyor = $totCoincidences;
            $pointsConveyor = array_key_exists($salesperson['id'], $retroPoints) ? $pointsConveyor + $retroPoints[$salesperson['id']]['countConveyor'] : $pointsConveyor;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countConveyor']){
                    $difPoints = $retroPoints[$salesperson['id']]['countConveyor'] - $totCoincidences;
                    $pointsConveyor = $totCoincidences + $difPoints;
                }
            }*/
            $pointsConveyor = $pointsConveyor * 10;

            //$pointsFieldsConveyor = $sumPopulateFields;
            $totCoincidences = $sumPopulateFields<0 ? 0 : $sumPopulateFields;
            $pointsFieldsConveyor = $totCoincidences;
            $pointsFieldsConveyor = array_key_exists($salesperson['id'], $retroPoints) ? $pointsFieldsConveyor + $retroPoints[$salesperson['id']]['countFieldsPopulated'] : $pointsFieldsConveyor;


            $totCoincidences = count($addReadingCoincidences);
            $pointsReading = $totCoincidences;
            $pointsReading = array_key_exists($salesperson['id'], $retroPoints) ? $pointsReading + $retroPoints[$salesperson['id']]['countReadings'] : $pointsReading;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countReadings']){
                    $difPoints = $retroPoints[$salesperson['id']]['countReadings'] - $totCoincidences;
                    $pointsReading = $totCoincidences + $difPoints;
                }
            }*/
            $pointsReading = $pointsReading * 100;

            $totCoincidences = count($addItemConveyorCoincidences);
            $pointsItemsConveyor = $totCoincidences;
            $pointsItemsConveyor = array_key_exists($salesperson['id'], $retroPoints) ? $pointsItemsConveyor + $retroPoints[$salesperson['id']]['countItems'] : $pointsItemsConveyor;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countItems']){
                    $difPoints = $retroPoints[$salesperson['id']]['countItems'] - $totCoincidences;
                    $pointsItemsConveyor = $totCoincidences + $difPoints;
                }
            }*/
            $pointsItemsConveyor = $pointsItemsConveyor * 5;

            $totCoincidences = count($addHistoryCoincidences);
            $pointsHistory = $totCoincidences;
            $pointsHistory = array_key_exists($salesperson['id'], $retroPoints) ? $pointsHistory + $retroPoints[$salesperson['id']]['countHistory'] : $pointsHistory;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countHistory']){
                    $difPoints = $retroPoints[$salesperson['id']]['countHistory'] - $totCoincidences;
                    $pointsHistory = $totCoincidences + $difPoints;
                }
            }*/
            $pointsHistory = $pointsHistory * 150;

            $totCoincidences = count($savingsStandbyCoincidences);
            $pointsSavingsStandby = $totCoincidences;
            $pointsSavingsStandby = array_key_exists($salesperson['id'], $retroPoints) ? $pointsSavingsStandby + $retroPoints[$salesperson['id']]['countSavingsStandBy'] : $pointsSavingsStandby;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countSavingsStandBy']){
                    $difPoints = $retroPoints[$salesperson['id']]['countSavingsStandBy'] - $totCoincidences;
                    $pointsSavingsStandby = $totCoincidences + $difPoints;
                }
            }*/
            $pointsSavingsStandby = $pointsSavingsStandby * 50;


            $totCoincidences = count($savingsApprovedCoincidences);
            $pointsSavingsApproved = $totCoincidences;
            $pointsSavingsApproved = array_key_exists($salesperson['id'], $retroPoints) ? $pointsSavingsApproved + $retroPoints[$salesperson['id']]['countSavingsApproved'] : $pointsSavingsApproved;
            /*if(array_key_exists($salesperson['id'], $retroPoints)){
                if($totCoincidences<$retroPoints[$salesperson['id']]['countSavingsApproved']){
                    $difPoints = $retroPoints[$salesperson['id']]['countSavingsApproved'] - $totCoincidences;
                    $pointsSavingsApproved = $totCoincidences + $difPoints;
                }
            }*/
            $pointsSavingsApproved = $pointsSavingsApproved * 250;


            $totalPoints = $pointsCustomer + $pointsConveyor + $pointsFieldsConveyor + $pointsReading + $pointsItemsConveyor + $pointsHistory + $pointsSavingsStandby + $pointsSavingsApproved;



            $scoreCard .= '<tr>';
            $scoreCard .= '<td>'.$salesperson['name'].'</td>';
            $scoreCard .= '<td>'.$pointsCustomer.'</td>';
            $scoreCard .= '<td>'.$pointsConveyor.'</td>';
            $scoreCard .= '<td>'.$pointsFieldsConveyor.'</td>';
            $scoreCard .= '<td>'.$pointsReading.'</td>';
            $scoreCard .= '<td>'.$pointsItemsConveyor.'</td>';
            $scoreCard .= '<td>'.$pointsSavingsStandby.'</td>';
            $scoreCard .= '<td>'.$pointsSavingsApproved.'</td>';
            $scoreCard .= '<td>'.$pointsHistory.'</td>';
            $scoreCard .= '<td><b>'.$totalPoints.'</b></td>';
            $scoreCard .= '<tr>';
        }
        echo $scoreCard;
    endif;
    ?>
    </tbody>
</table>