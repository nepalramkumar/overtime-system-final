<?php
namespace App\Http\Controllers;

class HrController extends Controller
{
    public static function getEmployeeList()
    {
        return HrController::getHrData('hr/employee');
    }

    public static function getDesignationList()
    {
        return HrController::getHrData('hr/designation');
    }

    public static function getDepartmentList()
    {
        return HrController::getHrData('hr/department');
    }

    public static function getLeaveDetails($empId)
    {
        return HrController::getHrData('hr/leave/' . $empId);
    }

    /**
     * Debug/exploration ko lagi थपिएको - data structure हेर्न मात्र।
     * empId, fromDate, toDate diyera specific leave records ल्याउने।
     */
    public static function getLeave($empId, $fromDate, $toDate)
    {
        $query = http_build_query([
            'empId'     => $empId,
            'fromDate'  => $fromDate,
            'toDate'    => $toDate,
        ]);
        return HrController::getHrData('hr/leave?' . $query);
    }

    /**
     * empId omit वा 0 दिए सबै employee को attendance आउँछ।
     */
    public static function getAttendance($fromDate, $toDate, $empId = null)
    {
        $params = [
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
        ];
        if (!is_null($empId)) {
            $params['empId'] = $empId;
        }
        $query = http_build_query($params);
        return HrController::getHrData('hr/attendance?' . $query);
    }

    /**
     * fromDate/toDate वा fiscalYear (BS) मध्ये एउटा दिने - fiscalYear दिए date range override हुन्छ।
     */
    public static function getHoliday($fromDate = null, $toDate = null, $fiscalYear = null)
    {
        $params = [];
        if (!is_null($fiscalYear)) {
            $params['fiscalYear'] = $fiscalYear;
        } else {
            $params['fromDate'] = $fromDate;
            $params['toDate']   = $toDate;
        }
        $query = http_build_query($params);
        return HrController::getHrData('hr/holiday?' . $query);
    }

    private static function getHrData($path)
    {
        $url = env('API_FETCH', 'https://jul.ican.net.np:12560/icanird/resta/') . $path;
        $token = HrController::getToken();
        $curl2 = curl_init($url);
        curl_setopt($curl2, CURLOPT_POST, FALSE);
        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl2, CURLOPT_HTTPHEADER, array('auth:' . $token));
        curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, 0);
        $response2 = curl_exec($curl2);
        curl_close($curl2);
       
        $result2 = json_decode($response2);
        //  dump($result2 );
        return $result2;
    }

    public static function getToken()
    {
        $url = env('API_FETCH', 'https://jul.ican.net.np:12560/icanird/resta/') . 'api/validate-request-exam';
        // dd($url);
        $data = array(
            'username' => env('HR_API_USERNAME'),
            'password' => env('HR_API_PASSWORD'),
        );
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Request Error:' . curl_error($curl);
            die();
        }
        curl_close($curl);
        // dd($response);
        $result = json_decode($response);
        
        
        return $result->data[0];
        
    }
    
}