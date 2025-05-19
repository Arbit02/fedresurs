<?php

function StartPythonScript() {
    $command = '"C:\\Users\\v_rov\\AppData\\Local\\Microsoft\\WindowsApps\\python.exe" "C:\\ospanel\\domains\\fedresurs_parser\\main.py" 2>&1';
    shell_exec($command) . "</pre>";
}
StartPythonScript();
function GetCookies($filePath)
{
    $json = file_get_contents($filePath);
    if ($json === false) {
        throw new Exception("Не удалось прочитать файл: " . $filePath);
    }

    $cookies = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Ошибка декодирования JSON: " . json_last_error_msg());
    }

    $cookieParts = [];
    foreach ($cookies as $cookie) {
        $cookieParts[] = $cookie['name'] . '=' . $cookie['value'];
    }
    return implode('; ', $cookieParts);
}


$cookiess = GetCookies('fedresurs_cookies.json');
function getGuid($searchParam, $searchType = 'inn'): string
{
    global $cookiess;
    $url = '';
    $referer = '';
    $type = '';
    $error = '';

    if (strlen($searchParam) == 0) {
        $error = 'Пустое значение параметра поиска';
    } else {
        if ($searchType === 'inn') {
            switch (strlen($searchParam)) {
                case 10:
                    $type = 'companies';
                    $url = "https://fedresurs.ru/backend/companies?limit=15&offset=0&searchString=$searchParam&isActive=true";
                    $referer = "https://fedresurs.ru/search/entity?code=$searchParam";
                    break;
                case 12:
                    $type = 'persons';
                    $url = "https://fedresurs.ru/backend/persons?limit=15&offset=0&searchString=$searchParam&isActive=true";
                    $referer = "https://fedresurs.ru/search/entity?code=$searchParam";
                    break;
                case 9:
                    $type = 'companies';
                    $url = "https://fedresurs.ru/backend/companies?limit=15&offset=0&searchString=0$searchParam&isActive=true";
                    $referer = "https://fedresurs.ru/search/entity?code=$searchParam";
                    break;
                case 11:
                    $type = 'persons';
                    $url = "https://fedresurs.ru/backend/persons?limit=15&offset=0&searchString=0$searchParam&isActive=true";
                    $referer = "https://fedresurs.ru/search/entity?code=$searchParam";
                    break;
                default:
                    throw new BadRequestException("Неверная длина ИНН!");
            }
        } elseif ($searchType === 'fio') {
            $type = 'persons';
            $encodedFio = urlencode($searchParam);
            $url = "https://fedresurs.ru/backend/persons?limit=15&offset=0&searchString=$encodedFio&isActive=true";
            $referer = "https://fedresurs.ru/search/person?name=$encodedFio";
        } else {
            throw new BadRequestException("Неверный тип поиска! Допустимые значения: 'inn' или 'fio'");
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json, text/plain, */*",
                "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Cookie: $cookiess",
                "Pragma: no-cache",
                "Referer: $referer",
                "Sec-Fetch-Dest: empty",
                "Sec-Fetch-Mode: cors",
                "Sec-Fetch-Site: same-origin",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                "X-KL-kis-Ajax-Request: Ajax_Request",
                "sec-ch-ua: ^\^Not_A",
                "sec-ch-ua-mobile: ?0",
                "sec-ch-ua-platform: ^\^Windows^^"
            ],
        ]);

        $result = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    }

    if ($err) {
        return json_encode(['error' => $err]);
    } else if (empty($result)) {
        return json_encode(['error' => 'Ничего не найдено']);
    } else {
        $arr = json_decode($result, true);
        return json_encode([
            'error' => $error,
            'guid' => $arr['pageData'][0]['guid'] ?? null,
            'type' => $type,
            'results' => $arr['pageData'] ?? []
        ]);
    }
}

function getPublicationsPerson($quid): string
{
    global $cookiess;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://fedresurs.ru/backend/persons/$quid/publications?limit=15&offset=0&searchPersonEfrsbMessage=true&searchAmReport=true&searchPersonBankruptMessage=true&searchMessageOnlyWithoutLegalCase=false&searchSfactsMessage=true&searchArbitrManagerMessage=true&searchTradeOrgMessage=true",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/plain, */*",
            "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Cookie: $cookiess",
            "Pragma: no-cache",
            "Referer: https://fedresurs.ru/person/$quid",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: same-origin",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "X-KL-kis-Ajax-Request: Ajax_Request",
            "sec-ch-ua: ^\^Not_A",
            "sec-ch-ua-mobile: ?0",
            "sec-ch-ua-platform: ^\^Windows^^"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode(['error' => "cURL Error #:" . $err]);
    } else {
        return json_encode(json_decode($response));
    }
}

function getPublicationsCompanies($quid): string
{
    global $cookiess;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://fedresurs.ru/backend/companies/$quid/publications?limit=50&offset=0&searchCompanyEfrsb=true&searchAmReport=true&searchFirmBankruptMessage=true&searchFirmBankruptMessageWithoutLegalCase=false&searchSfactsMessage=true&searchSroAmMessage=true&searchTradeOrgMessage=true",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/plain, */*",
            "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Cookie: $cookiess",
            "Pragma: no-cache",
            "Referer: https://fedresurs.ru/company/$quid",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: same-origin",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "X-KL-kis-Ajax-Request: Ajax_Request",
            "sec-ch-ua: ^\^Not_A",
            "sec-ch-ua-mobile: ?0",
            "sec-ch-ua-platform: ^\^Windows^^"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode(['error' => "cURL Error #:" . $err]);
    } else {
        return json_encode(json_decode($response));
    }
}

function getDetailCompany($guid): string
{
    global $cookiess;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://fedresurs.ru/backend/companies/$guid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/plain, */*",
            "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Cookie: $cookiess",
            "Pragma: no-cache",
            "Referer: https://fedresurs.ru/company/$guid",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: same-origin",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "X-KL-kis-Ajax-Request: Ajax_Request",
            "sec-ch-ua: ^\^Not_A",
            "sec-ch-ua-mobile: ?0",
            "sec-ch-ua-platform: ^\^Windows^^"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode(['error' => "cURL Error #:" . $err]);
    } else {
        $arr = json_decode($response, true);
        $arr['publications'] = json_decode(getPublicationsCompanies($guid));
        return json_encode($arr);
    }
}

function getDetailPerson($guid): string
{
    global $cookiess;
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://fedresurs.ru/backend/persons/$guid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/plain, */*",
            "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Cookie: $cookiess",
            "Pragma: no-cache",
            "Referer: https://fedresurs.ru/person/$guid",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: same-origin",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "X-KL-kis-Ajax-Request: Ajax_Request",
            "sec-ch-ua: ^\^Not_A",
            "sec-ch-ua-mobile: ?0",
            "sec-ch-ua-platform: ^\^Windows^^"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode(['error' => "cURL Error #:" . $err]);
    } else {
        $arr = json_decode($response, true);
        $arr['publications'] = json_decode(getPublicationsPerson($guid));
        return json_encode($arr);
    }
}

function search($line,$flag = 0): array
{

    if($flag==0){
        $res_array = json_decode(getGuid($line), true);
    }
    else{
        $res_array = json_decode(getGuid($line, 'fio'), true);
    }

    if (!empty($res_array['error'])) {
        return ['error' => $res_array['error']];
    }

    $guid = $res_array['guid'];
    $type = $res_array['type'];

    if ($type == 'companies') {
        return json_decode(getDetailCompany($guid), true);
    } else {
        return json_decode(getDetailPerson($guid), true);
    }
}

function isDomainAvailible($domain): bool
{
    if (!filter_var($domain, FILTER_VALIDATE_URL)) {
        return false;
    }

    $curl = curl_init($domain);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    $response = curl_exec($curl);
    curl_close($curl);

    return (bool)$response;
}

function getStatus(): array
{
    $message = isDomainAvailible('https://fedresurs.ru') ? "Сервис доступен" : "Сервис недоступен";
    return ['message' => $message];
}


function makeRequest($url, $referer = null) {
    global $cookiess;
    $curl = curl_init();

    $headers = [
        "Accept: application/json, text/plain, */*",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36",
        "X-KL-kis-Ajax-Request: Ajax_Request",
        "sec-ch-ua: ^\^Not_A",
        "sec-ch-ua-mobile: ?0",
        "sec-ch-ua-platform: ^\^Windows^^"
    ];

    if ($referer) {
        $headers[] = "Referer: $referer";
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_COOKIE => $cookiess,
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ['error' => $error];
    }

    return json_decode($response, true);
}

function getGeneralData($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}";
    return makeRequest($url, "https://fedresurs.ru/persons/{$id}");
}

function getIndividualEntrepreneurs($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/individual-entrepreneurs?limit=1&offset=0";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function getEncumbrances($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/encumbrances";
    return makeRequest($url, "https://fedresurs.ru/persons/{$id}");
}

function getPublication($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/publications?limit=15&offset=0";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function getSroMembership($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/sro-membership?limit=15&offset=0&isActive=true";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function getSroMembershipAu($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/sro-membership-au?limit=15&offset=0&isActive=true";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function getLicenses($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/licenses";
    return makeRequest($url, "https://fedresurs.ru/persons/{$id}");
}

function getBankruptcy($id) {
    $url = "https://fedresurs.ru/backend/persons/{$id}/bankruptcy";
    return makeRequest($url, "https://fedresurs.ru/persons/{$id}");
}

function getBiddings($id) {
    $url = "https://fedresurs.ru/backend/biddings?bankruptGuid={$id}&limit=3&offset=0";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function getAccountReceivables($id) {
    $url = "https://fedresurs.ru/backend/account-receivables?bankruptGuid={$id}&limit=3&offset=0";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function getPledgedSubjects($id) {
    $url = "https://fedresurs.ru/backend/pledged-subjects?bankruptGuid={$id}&limit=3&offset=0";
    $result = makeRequest($url, "https://fedresurs.ru/persons/{$id}");
    return $result['pageData'] ?? [];
}

function searchInBankrupt($params = []) {
    if (empty($params['inn']) && empty($params['name'])) {
        return ['error' => 'Введите ИНН или ФИО'];
    }

    $search = urlencode(!empty($params['inn']) ? $params['inn'] : $params['name']);
    $url = "https://bankrot.fedresurs.ru/backend/prsnbankrupts?searchString={$search}&isActiveLegalCase=null&limit=15&offset=0";
    $referer = "https://bankrot.fedresurs.ru/bankrupts?searchString={$search}&regionId=all&isActiveLegalCase=null";

    $result = makeRequest($url, $referer);

    if (isset($result['error'])) {
        return $result;
    }

    $data = $result['pageData'] ?? [];

    if (!empty($data)) {
        foreach ($data as $key => $item) {
            $guid = $item['guid'];
            $data[$key]['general_data'] = getGeneralData($guid);
            $data[$key]['individual-entrepreneurs'] = getIndividualEntrepreneurs($guid);
            $data[$key]['publications'] = getPublication($guid);
            $data[$key]['encumbrances'] = getEncumbrances($guid);
            $data[$key]['sro_membership'] = getSroMembership($guid);
            $data[$key]['sro_membership_au'] = getSroMembershipAu($guid);
            $data[$key]['licenses'] = getLicenses($guid);
            $data[$key]['bankruptcy'] = getBankruptcy($guid);
            $data[$key]['biddings'] = getBiddings($guid);
            $data[$key]['account-receivables'] = getAccountReceivables($guid);
            $data[$key]['pledged-subjects'] = getPledgedSubjects($guid);
            if (!empty($params['birthdate']) && empty($params['inn'])) {
                $itemBirthdate = $data[$key]['general_data']['birthdateBankruptcy'] ?? null;
                if ($itemBirthdate && date('Y-m-d', strtotime($itemBirthdate))) {
                    if (date('Y-m-d', strtotime($itemBirthdate)) != $params['birthdate']) {
                        unset($data[$key]);
                    }
                }
            }
        }
    }

    return array_values($data);
}

// Пример использования:
$result = searchInBankrupt([
    'inn' => '352403002533',
    'name' => 'Иванов Иван',
    'birthdate' => '1980-01-01'
]);

$result = search(370266257215);
print_r($result);
