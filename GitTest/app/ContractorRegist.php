<?php
/**
 * 下請け業者のテーブル
 *
 * @author  Miyamoto
 */
namespace App;

use DB;
use Illuminate\Support\Facades\Validator;

class ContractorRegist extends Model
{
    protected $fillable = [
        'name', 'zip', 'pref', 'village','addr', 'building','phone','manager_name','manager_mail','manager_password',
    ];

    public function validate()
    {
        $rules = [
            'name'          => ['required', 'string', 'max:128'],
            'zip'           => ['required', 'string', 'max:8'],
            'pref'          => ['required', 'int', 'max:10'],
            'village'       => ['required', 'string', 'max:128'],
            'addr'          => ['required', 'string', 'max:190'],
            'building'      => ['required', 'string', 'max:190'],
            'phone'         => ['required', 'string', 'max:11'],
            'manager_name'  => ['required', 'string', 'max:20'],
            'manager_password'  => ['required', 'string', 'max:20'],
        ];

        return Validator::make($this->getAttributes(), $rules);
    }


    static function get($name, $zip, $pref, $village, $addr, $building, $phone, $manager_name, $manager_mail, $manager_password)
    {
        $sql = "select * from contractorregisters";

        $where = '';
        $params = array();

        if ($name != '') {
            $where .= ($where != '' ? 'and ': 'where '). "name like ?\n";
            $params[] = '%'. $name. '%';
        }
        if ($zip != '') {
            $where .= ($where != '' ? 'and ': 'where '). "zip = ?\n";
            $params[] = $zip;
        }
        if ($pref != '') {
            $where .= ($where != '' ? 'and ': 'where '). "pref >= ?\n";
            $params[] = $pref;
        }
        if ($village != '') {
            $where .= ($where != '' ? 'and ': 'where '). "village <= ?\n";
            $params[] = $village;
        }
        if ($addr != '') {
            $where .= ($where != '' ? 'and ': 'where '). "addr like ?\n";
            $params[] = '%'. $addr. '%';
        }
        if ($building != '') {
            $where .= ($where != '' ? 'and ': 'where '). "building like ?\n";
            $params[] = '%'. $building. '%';
        }
        if ($phone != '') {
            $where .= ($where != '' ? 'and ': 'where '). "phone = ?\n";
            $params[] = $phone;
        }
        if ($manager_name != '') {
            $where .= ($where != '' ? 'and ': 'where '). "manager_name = ?\n";
            $params[] = $manager_name;
        }
        if ($manager_mail != '') {
            $where .= ($where != '' ? 'and ': 'where '). "manager_mail = ?\n";
            $params[] = $manager_mail;
        }
        if ($manager_password != '') {
            $where .= ($where != '' ? 'and ': 'where '). "manager_password = ?\n";
            $params[] = $manager_password;
        }

        $sql .= $where;
//echo $sql;
//print_r($params);

        $projects = DB::select($sql, $params);
//print_r($projects);
//exit();

        return $projects;
    }


    static function set($name, $zip, $pref, $village, $addr, $building, $phone, $manager_name, $manager_mail, $manager_password)
    {
        $data = self::get($name, $zip, $pref, $village, $addr, $building, $phone, $manager_name, $manager_mail, $manager_password);
//print_r($data);
        if (!empty($data)) {
//echo 'koko';
            return $data;
        }
        $sql  = "insert into contractorregisters (\n";
        $sql .= "name, zip, pref, village, addr, building, phone, manager_name, manager_mail, manager_password\n";
        $sql .= ") values (";
        $sql .= "?, ?, ?, ?, ?, ?, ?, ?, ?, ? sysdate()\n";
        $sql .= ")";

        $params = [$name, $zip, $pref, $village, $addr, $building, $phone, $manager_name, $manager_mail, $manager_password];
//echo $sql;
//exit();

        $ret = DB::insert($sql, $params);
    }
}
