class EGNCheck:
    def __init__(self):
        self.EGN_REGIONS = {}
        self.EGN_WEIGHTS = [2, 4, 8, 5, 10, 9, 7, 3, 6]
        self.MONTHS_BG = {
            1: "януари", 2: "февруари", 3: "март", 4: "април",
            5: "май", 6: "юни", 7: "юли", 8: "август", 9: "септември",
            10: "октомври", 11: "ноември", 12: "декември"
        }

        EGN_REGIONS = {
            "Благоевград": 43, "Бургас": 93, "Варна": 139, "Велико Търново": 169,
            "Видин": 183, "Враца": 217, "Габрово": 233, "Кърджали": 281,
            "Кюстендил": 301, "Ловеч": 319, "Монтана": 341, "Пазарджик": 377,
            "Перник": 395, "Плевен": 435, "Пловдив": 501, "Разград": 527,
            "Русе": 555, "Силистра": 575, "Сливен": 601, "Смолян": 623,
            "София - град": 721, "София - окръг": 751, "Стара Загора": 789,
            "Добрич (Толбухин)": 821, "Търговище": 843, "Хасково": 871,
            "Шумен": 903, "Ямбол": 925, "Друг/Неизвестен": 999
        }

        self.EGN_REGIONS = dict(sorted(EGN_REGIONS.items()))

    def check_is_valid(self, egn):
        EGN_WEIGHTS = self.EGN_WEIGHTS

        if len(egn) != 10:
            return False

        year = int(egn[0:2])
        month = int(egn[2:4])
        day = int(egn[4:6])

        if month > 40:
            if not (1 <= (month - 40) <= 12) or not (1 <= day <= 31):
                return False
        else:
            if month > 20:
                if not (1 <= (month - 20) <= 12) or not (1 <= day <= 31):
                    return False
            else:
                if not (1 <= month <= 12) or not (1 <= day <= 31):
                    return False

        checksum = int(egn[9])
        egn_sum = sum(int(egn[i]) * EGN_WEIGHTS[i] for i in range(9))
        valid_checksum = egn_sum % 11 if egn_sum % 11 != 10 else 0

        return checksum == valid_checksum

    def parse_egn(self, egn):
        EGN_REGIONS = self.EGN_REGIONS
        MONTHS_BG = self.MONTHS_BG

        if not self.check_is_valid(egn):
            return False

        ret = {}
        ret["year"] = int(egn[0:2])
        ret["month"] = int(egn[2:4])
        ret["day"] = int(egn[4:6])

        if ret["month"] > 40:
            ret["month"] -= 40
            ret["year"] += 2000
        elif ret["month"] > 20:
            ret["month"] -= 20
            ret["year"] += 1800
        else:
            ret["year"] += 1900

        ret["birthday_text"] = f"{ret['day']} {MONTHS_BG[ret['month']]} {ret['year']} г."

        region = int(egn[6:9])
        ret["region_num"] = region
        ret["sex"] = int(egn[8]) % 2
        ret["sex_text"] = "жена" if ret["sex"] else "мъж"

        for region_name, last_region_num in EGN_REGIONS.items():
            if region_name == "Друг/Неизвестен":
                continue

            if region >= ret["region_num"]:
                ret["region_text"] = region_name
                break

        if not ret["sex"]:
            region -= 1
        ret["birthnumber"] = (region - 1) // 2 + 1

        return ret

    def egn_info(self, egn):
        if not self.check_is_valid(egn):
            return f"<b>{egn}</b> невалиден ЕГН"

        data = self.parse_egn(egn)
        ret = (
            f"<b>{egn}</b> е ЕГН на <b>{data['sex_text']}</b>, "
            f"роден{'а' if data['sex'] else ''} на <b>{data['birthday_text']}</b> в "
            f"регион <b>{data['region_text']}</b> "
        )

        if data["birthnumber"] - 1:
            ret += f"като преди {'нея' if data['sex'] else 'него'} "
            if data["birthnumber"] - 1 > 1:
                ret += (
                    f"в този ден и регион са се родили <b>{data['birthnumber'] - 1}</b> "
                    f"{'' if data['sex'] else 'момичета' if data['birthnumber'] - 1 > 1 else 'момиче'}"
                )
            else:
                ret += (
                    f"в този ден и регион се е родило <b>1</b> "
                    f"{'' if data['sex'] else 'момиче' if data['birthnumber'] - 1 > 1 else 'момиче'}"
                )
        else:
            ret += f"като е {'била' if data['sex'] else 'билa'} "
            ret += f"<b>първото {'момиче' if data['sex'] else 'момче'}</b> "
            ret += "родено в този ден и регион"

        data['message'] = ret

        return data
