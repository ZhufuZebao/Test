<?php
/**
 * 案件を検索する
 *
 * @author  dong
 */

namespace App\Models;

use DB;
use phpDocumentor\Reflection\Types\Boolean;


class ProjectSearchModel extends Project
{
    public $searchArray;
    public $selectWord;
    public $searchType;


    public function init($params)
    {
        $this->searchArray = $params['searchArray'] ?? null;
        $this->selectWord = $params['selectWord'] ?? null;
        $this->searchType = $params['searchType'] ?? null;
    }


    public function search()
    {
        $q = Project::query();
        $input = $this->searchArray;
        $flg = $this->selectWord;
        $type = $this->searchType;
        if ($type == 'AND') {
            $q->where(function ($q2) use ($input, $flg) {
                if ($flg == 1) {
                    foreach ($input as $project_key => $project_value) {
                        if ($project_key == 'project') {
                            $q2->where(function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    if ($p2_key == 'address' and $p2_value) {
                                        $q3->where('address', 'LIKE', "%{$p2_value}%");
                                    }
                                    $q3->where($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'chief' and $project_value) {
                            $q2->whereHas('projectLocaleChief', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->where($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'customer' and $project_value) {

                            $q2->whereHas('customer', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->where('name', 'LIKE', "%{$p2_value}%");
                                }
                                $q3->orWhereHas('customerOffice', function ($q3) use ($project_value) {
                                    foreach ($project_value as $p2_key => $p2_value) {
                                        $q3->where('name', 'LIKE', "%{$p2_value}%");
                                    }
                                });
                            });

                        }
                    }
                } elseif ($flg == 2) {
                    foreach ($input as $project_key => $project_value) {
                        if ($project_key == 'builder' and $project_value) {
                            $q2->where(function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->where($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        }
                    }
                } elseif ($flg == 3) {
                    foreach ($input as $project_key => $project_value) {
                        if ($project_key == 'safety' and $project_value) {
                            $q2->where(function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->where($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'hospital' and $project_value) {
                            $q2->whereHas('projectHospital', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->where($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'trades_chief' and $project_value) {
                            $q2->whereHas('projectTradesChief', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->where($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        }
                    }
                }
            });
        }
        if ($type == 'OR') {
            $q->where(function ($q2) use ($input, $flg) {
                if ($flg == 1) {
                    foreach ($input as $project_key => $project_value) {
                        if ($project_key == 'project') {
                            $q2->orWhere(function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    if ($p2_key == 'housing' and $p2_value) {
                                        $q3->orWhere('address', 'LIKE', "%{$p2_value}%");
                                    }
                                    $q3->orWhere($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'chief' and $project_value) {
                            $q2->orWhereHas('projectLocaleChief', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->orWhere($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'customer' and $project_value) {
                            $q2->orWhereHas('customer', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->orWhere('name', 'LIKE', "%{$p2_value}%");
                                }
                                $q3->orWhereHas('customerOffice', function ($q3) use ($project_value) {
                                    foreach ($project_value as $p2_key => $p2_value) {
                                        $q3->orWhere('name', 'LIKE', "%{$p2_value}%");
                                    }
                                });
                            });

                        }
                    }
                } elseif ($flg == 2) {
                    foreach ($input as $project_key => $project_value) {
                        if ($project_key == 'builder' and $project_value) {
                            $q2->orWhere(function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->orWhere($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        }
                    }
                } elseif ($flg == 3) {
                    foreach ($input as $project_key => $project_value) {
                        if ($project_key == 'safety' and $project_value) {
                            $q2->orWhere(function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->orWhere($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'hospital' and $project_value) {
                            $q2->orWhereHas('projectHospital', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->orWhere($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        } elseif ($project_key == 'trades_chief' and $project_value) {
                            $q2->orWhereHas('projectTradesChief', function ($q3) use ($project_value) {
                                foreach ($project_value as $p2_key => $p2_value) {
                                    $q3->orWhere($p2_key, 'LIKE', "%{$p2_value}%");
                                }
                            });
                        }
                    }
                }
            });
        }
        return $q;
    }
}
