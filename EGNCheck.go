package main

import (
	"fmt"
	"sort"
)

type EGNCheck struct {
	EGN_REGIONS map[string]int
	EGN_WEIGHTS []int
	MONTHS_BG   map[int]string
}

func NewEGNCheck() *EGNCheck {
	egnCheck := &EGNCheck{
		EGN_REGIONS: make(map[string]int),
		EGN_WEIGHTS: []int{2, 4, 8, 5, 10, 9, 7, 3, 6},
		MONTHS_BG:   map[int]string{1: "януари", 2: "февруари", 3: "март", 4: "април", 5: "май", 6: "юни", 7: "юли", 8: "август", 9: "септември", 10: "октомври", 11: "ноември", 12: "декември"},
	}

	EGN_REGIONS := map[string]int{
		"Благоевград":       43,
		"Бургас":            93,
		"Варна":             139,
		"Велико Търново":    169,
		"Видин":             183,
		"Враца":             217,
		"Габрово":           233,
		"Кърджали":          281,
		"Кюстендил":         301,
		"Ловеч":             319,
		"Монтана":           341,
		"Пазарджик":         377,
		"Перник":            395,
		"Плевен":            435,
		"Пловдив":           501,
		"Разград":           527,
		"Русе":              555,
		"Силистра":          575,
		"Сливен":            601,
		"Смолян":            623,
		"София - град":      721,
		"София - окръг":     751,
		"Стара Загора":      789,
		"Добрич (Толбухин)": 821,
		"Търговище":         843,
		"Хасково":           871,
		"Шумен":             903,
		"Ямбол":             925,
		"Друг/Неизвестен":   999,
	}

	// Sort the EGN_REGIONS map by values
	keys := make([]string, 0, len(EGN_REGIONS))
	for k := range EGN_REGIONS {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	for _, key := range keys {
		egnCheck.EGN_REGIONS[key] = EGN_REGIONS[key]
	}

	return egnCheck
}

func (e *EGNCheck) checkIsValid(egn string) bool {
	EGN_WEIGHTS := e.EGN_WEIGHTS

	if len(egn) != 10 {
		return false
	}

	year := int(egn[0]-'0')*10 + int(egn[1]-'0')
	month := int(egn[2]-'0')*10 + int(egn[3]-'0')
	day := int(egn[4]-'0')*10 + int(egn[5]-'0')

	if month > 40 {
		if !(1 <= (month-40) && (month-40) <= 12) || !(1 <= day && day <= 31) {
			return false
		}
	} else if month > 20 {
		if !(1 <= (month-20) && (month-20) <= 12) || !(1 <= day && day <= 31) {
			return false
		}
	} else {
		if !(1 <= month && month <= 12) || !(1 <= day && day <= 31) {
			return false
		}
	}

	checksum := int(egn[9] - '0')
	egnSum := 0
	for i := 0; i < 9; i++ {
		egnSum += int(egn[i]-'0') * EGN_WEIGHTS[i]
	}

	validChecksum := egnSum % 11
	if validChecksum == 10 {
		validChecksum = 0
	}

	return checksum == validChecksum
}

func (e *EGNCheck) parseEgn(egn string) map[string]interface{} {
	EGN_REGIONS := e.EGN_REGIONS
	MONTHS_BG := e.MONTHS_BG

	if !e.checkIsValid(egn) {
		return nil
	}

	ret := make(map[string]interface{})
	ret["year"] = int(egn[0]-'0')*10 + int(egn[1]-'0')
	ret["month"] = int(egn[2]-'0')*10 + int(egn[3]-'0')
	ret["day"] = int(egn[4]-'0')*10 + int(egn[5]-'0')

	if ret["month"] > 40 {
		ret["month"] -= 40
		ret["year"] += 2000
	} else if ret["month"] > 20 {
		ret["month"] -= 20
		ret["year"] += 1800
	} else {
		ret["year"] += 1900
	}

	ret["birthday_text"] = fmt.Sprintf("%d %s %d г.", ret["day"], MONTHS_BG[ret["month"]], ret["year"])

	region := int(egn[6]-'0')*100 + int(egn[7]-'0')*10 + int(egn[8]-'0')
	ret["region_num"] = region
	ret["sex"] = int(egn[8]-'0') % 2
	ret["sex_text"] = "жена"
	if ret["sex"] == 0 {
		ret["sex_text"] = "мъж"
	}

	for regionName, lastRegionNum := range EGN_REGIONS {
		if regionName == "Друг/Неизвестен" {
			continue
		}

		if region >= lastRegionNum {
			ret["region_text"] = regionName
			break
		}
	}

	if ret["sex"] == 0 {
		region--
	}
	ret["birthnumber"] = (region - 1) / 2 + 1

	return ret
}

func (e *EGNCheck) egnInfo(egn string) map[string]interface{} {
	if !e.checkIsValid(egn) {
		return map[string]interface{}{"message": fmt.Sprintf("<b>%s</b> невалиден ЕГН", egn)}
	}

	data := e.parseEgn(egn)
	ret := fmt.Sprintf("<b>%s</b> е ЕГН на <b>%s</b>, роден%s на <b>%s</b> в регион <b>%s</b> ",
		egn, data["sex_text"], data["sex"], data["birthday_text"], data["region_text"])

	if data["birthnumber"].(float64)-1 > 0 {
		ret += fmt.Sprintf("като преди %s ", map[bool]string{true: "нея", false: "него"}[data["sex"].(bool)])
		if data["birthnumber"].(float64)-1 > 1 {
			ret += fmt.Sprintf("в този ден и регион са се родили <b>%d</b>", int(data["birthnumber"].(float64)-1))
			ret += map[bool]string{true: " момичета", false: " момчета"}[data["sex"].(bool)]
		} else {
			ret += fmt.Sprintf("в този ден и регион се е родило <b>1</b>")
			ret += map[bool]string{true: " момиче", false: " момче"}[data["sex"].(bool)]
		}
	} else {
		ret += fmt.Sprintf("като е %s ", map[bool]string{true: "била", false: "билa"}[data["sex"].(bool)])
		ret += fmt.Sprintf("<b>първото %s</b> ", map[bool]string{true: " момиче", false: " момче"}[data["sex"].(bool)])
		ret += "родено в този ден и регион"
	}

	data["message"] = ret

	return data
}

func main() {
	egnChecker := NewEGNCheck()
	egn := "1234567890" // Replace this with a valid or invalid EGN
	result := egnChecker.egnInfo(egn)
	fmt.Println(result["message"])
}
