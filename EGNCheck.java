import java.util.HashMap;
import java.util.Map;
import java.util.TreeMap;

public class EGNCheck {

    private final Map<String, Integer> EGN_REGIONS = new TreeMap<>();
    private final int[] EGN_WEIGHTS = {2, 4, 8, 5, 10, 9, 7, 3, 6};
    private final Map<Integer, String> MONTHS_BG = new HashMap<>();

    public EGNCheck() {
        initializeEGNRegions();
        initializeMonthsBG();
    }

    private void initializeEGNRegions() {
        EGN_REGIONS.put("Благоевград", 43);
        EGN_REGIONS.put("Бургас", 93);
        EGN_REGIONS.put("Варна", 139);
        EGN_REGIONS.put("Велико Търново", 169);
        EGN_REGIONS.put("Видин", 183);
        EGN_REGIONS.put("Враца", 217);
        EGN_REGIONS.put("Габрово", 233);
        EGN_REGIONS.put("Кърджали", 281);
        EGN_REGIONS.put("Кюстендил", 301);
        EGN_REGIONS.put("Ловеч", 319);
        EGN_REGIONS.put("Монтана", 341);
        EGN_REGIONS.put("Пазарджик", 377);
        EGN_REGIONS.put("Перник", 395);
        EGN_REGIONS.put("Плевен", 435);
        EGN_REGIONS.put("Пловдив", 501);
        EGN_REGIONS.put("Разград", 527);
        EGN_REGIONS.put("Русе", 555);
        EGN_REGIONS.put("Силистра", 575);
        EGN_REGIONS.put("Сливен", 601);
        EGN_REGIONS.put("Смолян", 623);
        EGN_REGIONS.put("София - град", 721);
        EGN_REGIONS.put("София - окръг", 751);
        EGN_REGIONS.put("Стара Загора", 789);
        EGN_REGIONS.put("Добрич (Толбухин)", 821);
        EGN_REGIONS.put("Търговище", 843);
        EGN_REGIONS.put("Хасково", 871);
        EGN_REGIONS.put("Шумен", 903);
        EGN_REGIONS.put("Ямбол", 925);
        EGN_REGIONS.put("Друг/Неизвестен", 999);
    }

    private void initializeMonthsBG() {
        MONTHS_BG.put(1, "януари");
        MONTHS_BG.put(2, "февруари");
        MONTHS_BG.put(3, "март");
        MONTHS_BG.put(4, "април");
        MONTHS_BG.put(5, "май");
        MONTHS_BG.put(6, "юни");
        MONTHS_BG.put(7, "юли");
        MONTHS_BG.put(8, "август");
        MONTHS_BG.put(9, "септември");
        MONTHS_BG.put(10, "октомври");
        MONTHS_BG.put(11, "ноември");
        MONTHS_BG.put(12, "декември");
    }

    private boolean checkIsValid(String egn) {
        if (egn.length() != 10) {
            return false;
        }

        int year = Integer.parseInt(egn.substring(0, 2));
        int month = Integer.parseInt(egn.substring(2, 4));
        int day = Integer.parseInt(egn.substring(4, 6));

        if (month > 40) {
            if (!(1 <= (month - 40) && (month - 40) <= 12) || !(1 <= day && day <= 31)) {
                return false;
            }
        } else if (month > 20) {
            if (!(1 <= (month - 20) && (month - 20) <= 12) || !(1 <= day && day <= 31)) {
                return false;
            }
        } else {
            if (!(1 <= month && month <= 12) || !(1 <= day && day <= 31)) {
                return false;
            }
        }

        int checksum = Integer.parseInt(egn.substring(9, 10));
        int egnSum = 0;

        for (int i = 0; i < 9; i++) {
            egnSum += Integer.parseInt(egn.substring(i, i + 1)) * EGN_WEIGHTS[i];
        }

        int validChecksum = egnSum % 11;
        if (validChecksum == 10) {
            validChecksum = 0;
        }

        return checksum == validChecksum;
    }

    private Map<String, Object> parseEgn(String egn) {
        if (!checkIsValid(egn)) {
            return null;
        }

        Map<String, Object> ret = new HashMap<>();
        ret.put("year", Integer.parseInt(egn.substring(0, 2)));
        ret.put("month", Integer.parseInt(egn.substring(2, 4)));
        ret.put("day", Integer.parseInt(egn.substring(4, 6)));

        if ((int) ret.get("month") > 40) {
            ret.put("month", (int) ret.get("month") - 40);
            ret.put("year", (int) ret.get("year") + 2000);
        } else if ((int) ret.get("month") > 20) {
            ret.put("month", (int) ret.get("month") - 20);
            ret.put("year", (int) ret.get("year") + 1800);
        } else {
            ret.put("year", (int) ret.get("year") + 1900);
        }

        ret.put("birthday_text", String.format("%d %s %d г.", ret.get("day"), MONTHS_BG.get(ret.get("month")), ret.get("year")));

        int region = Integer.parseInt(egn.substring(6, 9));
        ret.put("region_num", region);
        ret.put("sex", Integer.parseInt(egn.substring(8, 9)) % 2);
        ret.put("sex_text", ret.get("sex").equals(0) ? "жена" : "мъж");

        for (Map.Entry<String, Integer> entry : EGN_REGIONS.entrySet()) {
            if (entry.getKey().equals("Друг/Неизвестен")) {
                continue;
            }

            if (region >= entry.getValue()) {
                ret.put("region_text", entry.getKey());
                break;
            }
        }

        if (ret.get("sex").equals(0)) {
            region--;
        }

        ret.put("birthnumber", (region - 1) / 2 + 1);

        return ret;
    }

    public Map<String, Object> egnInfo(String egn) {
        Map<String, Object> result = new HashMap<>();

        if (!checkIsValid(egn)) {
            result.put("message", String.format("<b>%s</b> невалиден ЕГН", egn));
            return result;
        }

        Map<String, Object> data = parseEgn(egn);
        String message = String.format("<b>%s</b> е ЕГН на <b>%s</b>, роден%s на <b>%s</b> в регион <b>%s</b> ",
                egn, data.get("sex_text"), data.get("sex"), data.get("birthday_text"), data.get("region_text"));

        if ((int) data.get("birthnumber") - 1 > 0) {
            message += String.format("като преди %s ", data.get("sex").equals(1) ? "нея" : "него");
            if ((int) data.get("birthnumber") - 1 > 1) {
                message += String.format("в този ден и регион са се родили <b>%d</b>", (int) data.get("birthnumber") - 1);
                message += data.get("sex").equals(1) ? " момичета" : " момчета";
            } else {
                message += String.format("в този ден и регион се е родило <b>1</b>");
                message += data.get("sex").equals(1) ? " момиче" : " момче";
            }
        } else {
            message += String.format("като е %s ", data.get("sex").equals(1) ? "била" : "билa");
            message += String.format("<b>първото %s</b> ", data.get("sex").equals(1) ? " момиче" : " момче");
            message += "родено в този ден и регион";
        }

        data.put("message", message);
        return data;
    }

    public static void main(String[] args) {
        EGNCheck egnCheck = new EGNCheck();
        String egn = "1234567890"; // Replace this with a valid or invalid EGN
        Map<String, Object> result = egnCheck.egnInfo(egn);
        System.out.println(result.get("message"));
    }
}
