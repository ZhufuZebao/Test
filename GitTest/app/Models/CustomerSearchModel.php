<?php
/**
 * 案件を検索する
 */
namespace App\Models;

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

    public function detailedSearch()
    {
        $q = Customer::query();
        $input = $this->searchArray['customer'];

        if ($this->searchArray['searchType'] == 'AND') {
            //AND検索
            $q->where(function ($q2) use ($input) {
                foreach ($input as $customer_key => $customer_value) {
                    if ($customer_key == 'office') {
                        $q2->whereHas('offices', function ($q3) use ($customer_value) {
                            foreach ($customer_value as $office_key => $office_value) {
                                if ($office_key and is_string($office_value)) {
                                    $q3->where($office_key, 'LIKE', "%{$office_value}%");
                                } elseif (!is_string($office_value)) {
                                    foreach ($office_value as $people_billing_key => $people_billing_value) {
                                        if ($people_billing_value == 'people') {
                                            $q3->whereHas('people', function ($q4) use ($people_billing_value) {
                                                foreach ($people_billing_value as $people_key => $people_value) {
                                                    if ($people_value)
                                                        $q4->where($people_key, 'LIKE', "%{$people_value}%");
                                                };
                                            });
                                        }
                                        if ($people_billing_value == 'billing') {
                                            $q3->whereHas('billings', function ($q4) use ($people_billing_value) {
                                                foreach ($people_billing_value as $billing_key => $billing_value) {
                                                    if ($billing_value)
                                                        $q4->where($billing_key, 'LIKE', "%{$billing_value}%");
                                                };
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        if ($customer_key and is_string($customer_value))
                            $q2->where($customer_key, 'LIKE', "%{$customer_value}%");
                    };
                };
            });
        } elseif ($this->searchArray['searchType'] == 'OR') {
            //OR検索
            $q->where(function ($q2) use ($input) {
                foreach ($input as $customer_key => $customer_value) {
                    if ($customer_key == 'office') {
                        $q2->orWhereHas('offices', function ($q3) use ($customer_value) {
                            foreach ($customer_value as $office_key => $office_value) {
                                if ($office_key and is_string($office_value)) {
                                    $q3->orWhere($office_key, 'LIKE', "%{$office_value}%");
                                } elseif (!is_string($office_value)) {
                                    foreach ($office_value as $people_billing_key => $people_billing_value) {
                                        if ($people_billing_value == 'people') {
                                            $q3->orWhereHas('people', function ($q4) use ($people_billing_value) {
                                                foreach ($people_billing_value as $people_key => $people_value) {
                                                    if ($people_value)
                                                        $q4->orWhere($people_key, 'LIKE', "%{$people_value}%");
                                                };
                                            });
                                        }
                                        if ($people_billing_value == 'billing') {
                                            $q3->orWhereHas('billings', function ($q4) use ($people_billing_value) {
                                                foreach ($people_billing_value as $billing_key => $billing_value) {
                                                    if ($billing_value)
                                                        $q4->orWhere($billing_key, 'LIKE', "%{$billing_value}%");
                                                };
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        if ($customer_key and is_string($customer_value))
                            $q2->orWhere($customer_key, 'LIKE', "%{$customer_value}%");
                    };
                };
            });
        };
        return $q;
    }

}
