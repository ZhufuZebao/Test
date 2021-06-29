<?php
/**
 * 案件を検索する
 *
 * @author  Reiko Mori <reikomori@rocketmail.com>
 */
namespace App;

class CustomerSearchModel
{
    public $searchword;
    public $target;
    public $searchArray;

    public function init($params)
    {
        $this->searchword = $params['searchword'] ?? null;
        $this->target = $params['target'] ?? null;
        $this->searchArray = $params['searchArray'] ?? null;
    }

    public function search()
    {
        $q = Customer::query();

        if($this->searchword)
        {
            $words  = explode(' ', $this->searchword);
            $targets = explode('_', $this->target);
            $params = [];

            foreach($words as $word) {
                /* 絞込みがすべての場合、customer.{id,name} 
                 * またはcustomer.office.{name,tel,fax}
                 * またはcustomer.people.nameのうち
                 * どこか１カ所に一致すればOK
                 */
                if($targets[0]=='all'){
                    $q->where(function($q2) use($word){
                        $q2->orWhere('id', 'LIKE', "%{$word}%");
                        $q2->orWhere('name', 'LIKE', "%{$word}%");
                        $q2->orWhere('phonetic', 'LIKE', "%{$word}%");
                        $q2->orWhereHas('offices', function($q3) use($word) {
                            $q3->where('name', 'LIKE', "%{$word}%");
                            $q3->orWhere('tel', 'LIKE', "%{$word}%");
                            $q3->orWhere('fax', 'LIKE', "%{$word}%");
                            $q3->orWhereHas('people', function($q4) use($word) {
                                $q4->where('name', 'LIKE', "%{$word}%");
                            });
                        });
                        
                    });
                }elseif ($targets[0]=='customers') {
                    /* 絞込みが顧客名の場合、customer.{name,phonetic} の
                     * どちらか１カ所に一致すればOK
                     */
                    $q->where(function($q2) use($word,$targets){
                        $q2->orWhere('name', 'LIKE', "%{$word}%");
                        $q2->orWhere('phonetic', 'LIKE', "%{$word}%");
                    });
                }elseif ($targets[0]=='offices') {
                    //絞り込みが事業所系の場合、選択肢がそのまま対象
                    $q->where(function($q2) use($word,$targets){
                        $q2->WhereHas('offices', function($q3) use($word,$targets) {
                            $q3->Where($targets[1],'LIKE', "%{$word}%");
                        });
                    });
                }elseif ($targets[0]=='people') {
                    //絞り込みが担当者系の場合、選択肢がそのまま対象
                    $q->where(function($q2) use($word,$targets){
                        $q2->WhereHas('offices', function($q3) use($word,$targets) {
                            $q3->WhereHas('people', function($q4) use($word,$targets) {
                                $q4->where($targets[1], 'LIKE', "%{$word}%");
                            });
                        });
                    });
                };
            }
        }

        return $q;
    }

    public function detailedSearch()
    {
        $q = Customer::query();
        $input = $this->searchArray['customer'];
        $flg = $this->searchArray['enableFlg'];
        
        if($this->searchArray['searchType']=='AND'){
            //AND検索
            $q->where(function($q2) use($input,$flg){
                foreach($input as $customer_key => $customer_value){
                    if($customer_key=='office'){
                        $q2->whereHas('offices', function($q3) use($customer_value,$flg) {
                            foreach($customer_value as $office_key => $office_value){
                                if($office_key=='people'){
                                    $q3->whereHas('people', function($q4) use($office_value,$flg) {
                                        foreach($office_value as $people_key => $people_value){
                                            if($flg['customer']['office']['people'][$people_key])
                                                $q4->where($people_key, 'LIKE', "%{$people_value}%");
                                        };
                                    });
                                }elseif($office_key=='billing'){
                                    $q3->whereHas('billings', function($q4) use($office_value,$flg) {
                                        foreach($office_value as $billing_key => $billing_value){
                                            if($flg['customer']['office']['billing'][$billing_key])
                                                $q4->where($billing_key, 'LIKE', "%{$billing_value}%");
                                        };
                                    });
                                }else{
                                    if($flg['customer']['office'][$office_key])
                                    $q3->where($office_key, 'LIKE', "%{$office_value}%");
                                };
                            };
                        });
                    }else{
                        if($flg['customer'][$customer_key])
                            $q2->where($customer_key, 'LIKE', "%{$customer_value}%");
                    };
                };
            });
        }elseif($this->searchArray['searchType']=='OR'){
            //OR検索
            $q->where(function($q2) use($input,$flg){
                foreach($input as $customer_key => $customer_value){
                    if($customer_key=='office'){
                        if(in_array(true,$flg['customer']['office'],true) || in_array(true,$flg['customer']['office']['people'],true) || in_array(true,$flg['customer']['office']['billing'],true)){
                            $q2->orWhereHas('offices', function($q3) use($customer_value,$flg) {
                                $officeFirst = true;
                                foreach($customer_value as $office_key => $office_value){
                                    if($office_key=='people'){
                                        if(in_array(true,$flg['customer']['office']['people'],true)){
                                            $q3->orWhereHas('people', function($q4) use($office_value,$flg) {
                                                $peopleFirst = true;
                                                foreach($office_value as $people_key => $people_value){
                                                    if($flg['customer']['office']['people'][$people_key]){
                                                        if($peopleFirst == true){
                                                            $peopleFirst = false;
                                                            $q4->where($people_key, 'LIKE', "%{$people_value}%");
                                                        }else $q4->orWhere($people_key, 'LIKE', "%{$people_value}%");
                                                    }
                                                };
                                            });
                                        };
                                    }elseif($office_key=='billing'){
                                        if(in_array(true,$flg['customer']['office']['billing'],true)){
                                            $q3->orWhereHas('billings', function($q4) use($office_value,$flg) {
                                                $billingFirst = true;
                                                foreach($office_value as $billing_key => $billing_value){
                                                    if($flg['customer']['office']['billing'][$billing_key]){
                                                        if($billingFirst == true){
                                                            $billingFirst = false;
                                                            $q4->where($billing_key, 'LIKE', "%{$billing_value}%");
                                                        }else $q4->orWhere($billing_key, 'LIKE', "%{$billing_value}%");
                                                    }
                                                };
                                            });
                                        };
                                    }else{
                                        if($flg['customer']['office'][$office_key]){
                                            if($officeFirst == true){
                                                $officeFirst = false;
                                                $q3->where($office_key, 'LIKE', "%{$office_value}%");
                                            }else $q3->orWhere($office_key, 'LIKE', "%{$office_value}%");
                                        }
                                    };
                                };
                            });
                        };
                    }else{
                        if($flg['customer'][$customer_key])
                            $q2->orWhere($customer_key, 'LIKE', "%{$customer_value}%");
                    };
                };
            });
        };
        return $q;
    }

}
