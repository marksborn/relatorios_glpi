<?php

if (!defined('GLPI_ROOT')){
    die("Sorry, tou can't access this file directly")
}

class PluginMreportingTimeSheet Extends PluginMreportingBaseclass {

    function reportVstackbarAnalytics($config = []) {
        global $DB;
  
        $_SESSION['mreporting_selector']['reportVstackbarAnalytics'] =
           ['dateinterval', 'allstates', 'multiplegroupassign', 'category'];
  
        $tab = [];
        $datas = [];
  
        if (!isset($_SESSION['mreporting_values']['date2'.$config['randname']])) {
           $_SESSION['mreporting_values']['date2'.$config['randname']] = strftime("%Y-%m-%d");
        }
  
        foreach ($this->status as $current_status) {
           if ($_SESSION['mreporting_values']['status_'.$current_status] == '1') {
              $status_name = Ticket::getStatus($current_status);
              $sql_status = "SELECT
                       DISTINCT g.completename AS group_name,
                       COUNT(DISTINCT glpi_tickets.id) AS nb
                    FROM glpi_tickets
                    {$this->sql_join_gt}
                    {$this->sql_join_g}
                    WHERE {$this->sql_date_create}
                       AND glpi_tickets.entities_id IN ({$this->where_entities})
                       AND glpi_tickets.is_deleted = '0'
                       AND glpi_tickets.status = $current_status
                       AND {$this->sql_type}
                       AND {$this->sql_itilcat}
                       AND {$this->sql_group_assign}
                    GROUP BY group_name
                    ORDER BY group_name";
              $res = $DB->query($sql_status);
              while ($data = $DB->fetchAssoc($res)) {
                 if (empty($data['group_name'])) {
                    $data['group_name'] = __("None");
                 }
                 $tab[$data['group_name']][$status_name] = $data['nb'];
              }
           }
        }
  
        //ascending order of datas by date
        ksort($tab);
  
        //fill missing datas with zeros
        $datas = $this->fillStatusMissingValues($tab);
  
        return $datas;
     }
}