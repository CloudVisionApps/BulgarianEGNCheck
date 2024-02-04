<?php

class EGNCheck
{
    public $EGN_REGIONS = array();

    public $EGN_WEIGHTS = array(2,4,8,5,10,9,7,3,6);

    public $MONTHS_BG = array(1=>"януари",2=>"февруари",3=>"март",4=>"април",
        5=>"май",6=>"юни",7=>"юли",8=>"август",9=>"септември",
        10 => "октомври",11=>"ноември",12=>"декември");

    public function __construct()
    {

        /* Отделени номера */
        $EGN_REGIONS["Благоевград"]       = 43;  /* от 000 до 043 */
        $EGN_REGIONS["Бургас"]            = 93;  /* от 044 до 093 */
        $EGN_REGIONS["Варна"]             = 139; /* от 094 до 139 */
        $EGN_REGIONS["Велико Търново"]    = 169; /* от 140 до 169 */
        $EGN_REGIONS["Видин"]             = 183; /* от 170 до 183 */
        $EGN_REGIONS["Враца"]             = 217; /* от 184 до 217 */
        $EGN_REGIONS["Габрово"]           = 233; /* от 218 до 233 */
        $EGN_REGIONS["Кърджали"]          = 281; /* от 234 до 281 */
        $EGN_REGIONS["Кюстендил"]         = 301; /* от 282 до 301 */
        $EGN_REGIONS["Ловеч"]             = 319; /* от 302 до 319 */
        $EGN_REGIONS["Монтана"]           = 341; /* от 320 до 341 */
        $EGN_REGIONS["Пазарджик"]         = 377; /* от 342 до 377 */
        $EGN_REGIONS["Перник"]            = 395; /* от 378 до 395 */
        $EGN_REGIONS["Плевен"]            = 435; /* от 396 до 435 */
        $EGN_REGIONS["Пловдив"]           = 501; /* от 436 до 501 */
        $EGN_REGIONS["Разград"]           = 527; /* от 502 до 527 */
        $EGN_REGIONS["Русе"]              = 555; /* от 528 до 555 */
        $EGN_REGIONS["Силистра"]          = 575; /* от 556 до 575 */
        $EGN_REGIONS["Сливен"]            = 601; /* от 576 до 601 */
        $EGN_REGIONS["Смолян"]            = 623; /* от 602 до 623 */
        $EGN_REGIONS["София - град"]      = 721; /* от 624 до 721 */
        $EGN_REGIONS["София - окръг"]     = 751; /* от 722 до 751 */
        $EGN_REGIONS["Стара Загора"]      = 789; /* от 752 до 789 */
        $EGN_REGIONS["Добрич (Толбухин)"] = 821; /* от 790 до 821 */
        $EGN_REGIONS["Търговище"]         = 843; /* от 822 до 843 */
        $EGN_REGIONS["Хасково"]           = 871; /* от 844 до 871 */
        $EGN_REGIONS["Шумен"]             = 903; /* от 872 до 903 */
        $EGN_REGIONS["Ямбол"]             = 925; /* от 904 до 925 */
        $EGN_REGIONS["Друг/Неизвестен"]   = 999; /* от 926 до 999 - Такъв регион понякога се ползва при
                                                                родени преди 1900, за родени в чужбина
                                                                или ако в даден регион се родят повече
                                                                деца от предвиденото. Доколкото ми е
                                                                известно няма правило при ползването
                                                                на 926 - 999 */
        asort($EGN_REGIONS);
        $this->EGN_REGIONS = $EGN_REGIONS;

        $EGN_REGIONS_LAST_NUM  = array();
        $EGN_REGIONS_FIRST_NUM = array();
        $first_region_num = 0;
        foreach ($EGN_REGIONS as $region => $last_region_num) {
            $EGN_REGIONS_FIRST_NUM[$first_region_num] = $last_region_num;
            $EGN_REGIONS_LAST_NUM[$last_region_num] = $first_region_num;
            $first_region_num = $last_region_num+1;
        }

    }

    function checkIsValid($egn) { // egn_valid

        $EGN_WEIGHTS = $this->EGN_WEIGHTS;

        if (strlen($egn) != 10) {
            return false;
        }
        $year = substr($egn,0,2);
        $mon  = substr($egn,2,2);
        $day  = substr($egn,4,2);
        if ($mon > 40) {
            if (!checkdate($mon-40, $day, $year+2000)) return false;
        } else {
            if ($mon > 20) {
                if (!checkdate($mon - 20, $day, $year + 1800)) return false;
            } else {
                if (!checkdate($mon, $day, $year + 1900)) return false;
            }
        }
        $checksum = substr($egn,9,1);
        $egnsum = 0;
        for ($i=0;$i<9;$i++) {
            $egnsum += substr($egn, $i, 1) * $EGN_WEIGHTS[$i];
        }
        $valid_checksum = $egnsum % 11;
        if ($valid_checksum == 10) {
            $valid_checksum = 0;
        }
        if ($checksum == $valid_checksum) {
            return true;
        }
        return false;
    }

    function parseEgn($egn) {

        $EGN_REGIONS = $this->EGN_REGIONS;
        $MONTHS_BG = $this->MONTHS_BG;

        if (!$this->checkIsValid($egn)) {
            return false;
        }

        $ret = array();
        $ret["year"]  = substr($egn,0,2);
        $ret["month"] = substr($egn,2,2);
        $ret["day"]   = substr($egn,4,2);
        if ($ret["month"] > 40) {
            $ret["month"] -= 40;
            $ret["year"]  += 2000;
        } else {
            if ($ret["month"] > 20) {
                $ret["month"] -= 20;
                $ret["year"] += 1800;
            } else {
                $ret["year"] += 1900;
            }
        }
        $ret["birthday_text"] = (int)$ret["day"]." ".$MONTHS_BG[(int)$ret["month"]]." ".$ret["year"]." г.";
        $region = substr($egn,6,3);
        $ret["region_num"] = $region;
        $ret["sex"] = substr($egn,8,1) % 2;
        $ret["sex_text"] = "жена";
        if (!$ret["sex"]) {
            $ret["sex_text"] = "мъж";
        }
        $first_region_num = 0;
        foreach ($EGN_REGIONS as $region_name => $last_region_num) {
            if ($region >= $first_region_num && $region <= $last_region_num) {
                $ret["region_text"] = $region_name;
                break;
            }
            $first_region_num = $last_region_num+1;
        }
        if (substr($egn,8,1) % 2 != 0) {
            $region--;
        }
        $ret["birthnumber"] = ($region - $first_region_num) / 2 + 1;

        return $ret;
    }

    function egnInfo($egn) {
        if (!$this->checkIsValid($egn)) {
            return "<b>" . htmlspecialchars($egn) ."</b> невалиден ЕГН";
        }
        $data = $this->parseEgn($egn);
        $ret  = "<b>".htmlspecialchars($egn)."</b> е ЕГН на <b>{$data['sex_text']}</b>, ";
        $ret .= "роден".($data["sex"]?"а":"")." на <b>{$data['birthday_text']}</b> в ";
        $ret .= "регион <b>{$data['region_text']}</b> ";
        if ($data["birthnumber"]-1) {
            $ret .= "като преди ".($data["sex"]?"нея":"него")." ";
            if ($data["birthnumber"]-1 > 1) {
                $ret .= "в този ден и регион са се родили <b>".($data["birthnumber"]-1)."</b>";
                $ret .= $data["sex"]?" момичета":" момчета";
            } else {
                $ret .= "в този ден и регион се е родило <b>1</b>";
                $ret .= $data["sex"]?" момиче":" момче";
            }
        } else {
            $ret .= "като е ".($data["sex"]?"била":"бил")." ";
            $ret .= "<b>първото ".($data["sex"]?" момиче":" момче")."</b> ";
            $ret .= "родено в този ден и регион";
        }

        $data['message'] = $ret;

        return $data;
    }
}
