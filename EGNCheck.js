class EGNCheck {
    constructor() {
        this.EGN_REGIONS = {};
        this.EGN_WEIGHTS = [2, 4, 8, 5, 10, 9, 7, 3, 6];
        this.MONTHS_BG = {
            1: "януари", 2: "февруари", 3: "март", 4: "април",
            5: "май", 6: "юни", 7: "юли", 8: "август", 9: "септември",
            10: "октомври", 11: "ноември", 12: "декември"
        };

        const EGN_REGIONS = {
            "Благоевград": 43, "Бургас": 93, "Варна": 139, "Велико Търново": 169,
            "Видин": 183, "Враца": 217, "Габрово": 233, "Кърджали": 281,
            "Кюстендил": 301, "Ловеч": 319, "Монтана": 341, "Пазарджик": 377,
            "Перник": 395, "Плевен": 435, "Пловдив": 501, "Разград": 527,
            "Русе": 555, "Силистра": 575, "Сливен": 601, "Смолян": 623,
            "София - град": 721, "София - окръг": 751, "Стара Загора": 789,
            "Добрич (Толбухин)": 821, "Търговище": 843, "Хасково": 871,
            "Шумен": 903, "Ямбол": 925, "Друг/Неизвестен": 999
        };

        // Sort the EGN_REGIONS object by values
        Object.entries(EGN_REGIONS).sort((a, b) => a[1] - b[1]).forEach(([key, value]) => {
            this.EGN_REGIONS[key] = value;
        });
    }

    checkIsValid(egn) {
        const EGN_WEIGHTS = this.EGN_WEIGHTS;

        if (egn.length !== 10) {
            return false;
        }

        const year = parseInt(egn.slice(0, 2), 10);
        const month = parseInt(egn.slice(2, 4), 10);
        const day = parseInt(egn.slice(4, 6), 10);

        if (month > 40) {
            if (!(1 <= (month - 40) <= 12) || !(1 <= day <= 31)) {
                return false;
            }
        } else if (month > 20) {
            if (!(1 <= (month - 20) <= 12) || !(1 <= day <= 31)) {
                return false;
            }
        } else {
            if (!(1 <= month <= 12) || !(1 <= day <= 31)) {
                return false;
            }
        }

        const checksum = parseInt(egn[9], 10);
        const egnSum = EGN_WEIGHTS.reduce((sum, weight, i) => sum + parseInt(egn[i], 10) * weight, 0);
        const validChecksum = egnSum % 11 === 10 ? 0 : egnSum % 11;

        return checksum === validChecksum;
    }

    parseEgn(egn) {
        const EGN_REGIONS = this.EGN_REGIONS;
        const MONTHS_BG = this.MONTHS_BG;

        if (!this.checkIsValid(egn)) {
            return false;
        }

        const ret = {};
        ret.year = parseInt(egn.slice(0, 2), 10);
        ret.month = parseInt(egn.slice(2, 4), 10);
        ret.day = parseInt(egn.slice(4, 6), 10);

        if (ret.month > 40) {
            ret.month -= 40;
            ret.year += 2000;
        } else if (ret.month > 20) {
            ret.month -= 20;
            ret.year += 1800;
        } else {
            ret.year += 1900;
        }

        ret.birthday_text = `${ret.day} ${MONTHS_BG[ret.month]} ${ret.year} г.`;

        const region = parseInt(egn.slice(6, 9), 10);
        ret.region_num = region;
        ret.sex = parseInt(egn[8], 10) % 2;
        ret.sex_text = ret.sex ? "жена" : "мъж";

        for (const [regionName, lastRegionNum] of Object.entries(EGN_REGIONS)) {
            if (regionName === "Друг/Неизвестен") {
                continue;
            }

            if (region >= lastRegionNum) {
                ret.region_text = regionName;
                break;
            }
        }

        if (!ret.sex) {
            region--;
        }
        ret.birthnumber = Math.floor((region - 1) / 2) + 1;

        return ret;
    }

    egnInfo(egn) {
        if (!this.checkIsValid(egn)) {
            return `<b>${egn}</b> невалиден ЕГН`;
        }

        const data = this.parseEgn(egn);
        let ret = `<b>${egn}</b> е ЕГН на <b>${data.sex_text}</b>, ` +
            `роден${data.sex ? "а" : ""} на <b>${data.birthday_text}</b> в ` +
            `регион <b>${data.region_text}</b> `;

        if (data.birthnumber - 1) {
            ret += `като преди ${data.sex ? "нея" : "него"} `;
            if (data.birthnumber - 1 > 1) {
                ret += `в този ден и регион са се родили <b>${data.birthnumber - 1}</b>` +
                    `${data.sex ? " момичета" : " момчета"}`;
            } else {
                ret += `в този ден и регион се е родило <b>1</b>` +
                    `${data.sex ? " момиче" : " момче"}`;
            }
        } else {
            ret += `като е ${data.sex ? "била" : "билa"} ` +
                `<b>първото ${data.sex ? " момиче" : " момче"}</b> ` +
                `родено в този ден и регион`;
        }

        data.message = ret;

        return data;
    }
}

// Example usage
const egnChecker = new EGNCheck();
const egn = "1234567890"; // Replace this with a valid or invalid EGN
const result = egnChecker.egnInfo(egn);
console.log(result.message);
