<?php
$publicCard = [[1, 9], [3, 14], [1, 2], [2, 6], [1, 4]];
$selfCard   = [[2, 10], [2, 14]];

$allCard = array_merge($publicCard, $selfCard);


$cards = sortCard($allCard);

//print_r($cards);
$cardType = ['2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10,'11'=>'J','12'=>'Q','13'=>'K','14'=>'A'];
$new   = array_shift($cards);
print_r($new);

$cardHtml = '';
foreach ($new as $k => $v) {
    $cardHtml .= $cardType[$v[1]].',';
}
echo $cardHtml;
echo getTypeNum($new);



function getGroup($allCard, $num)
{
    $r = [];
    $n = count($allCard);
    if ($n < $num || $num <= 0) {
        return $r;
    }
    foreach ($allCard as $k => $v) {
        if ($num == 1) {
            $r[] = [$v];
        } else {
            $temp = array_slice($allCard, $k + 1);
            $c    = getGroup($temp, $num - 1);
            foreach ($c as $key => $val) {
                $r[] = array_merge([$v], $val);
            }
        }
    }
    return $r;
}

function sortCard($allCard)
{
    $cards = getGroup($allCard, 5);

    usort($cards, function ($prev, $next) {
        $type1 = getTypeNum($prev);
        $type2 = getTypeNum($next);
        if ($type2 > $type1) {
            return 1;
        }
        if ($type2 < $type1) {
            return -1;
        }
        if ($type1 == $type2) //同一种牌型比较大小
        {
            return equalCardType($type1, $prev, $next);
        }
    });
    return $cards;
}


function getTypeNum($card)
{
    $nums   = getNums($card);
    $colors = getColors($card);
    if ($nums == [10, 11, 12, 13, 14] && checkFlush($colors)) //皇家同花顺
    {
        return 10;
    }
    if (checkStraight($nums) && checkFlush($colors)) //同花顺
    {
        return 9;
    }
    if (checkSame($nums, 4) == 1) //四条
    {
        return 8;
    }
    if (checkSame($nums, 3) == 1 && checkSame($nums, 2) == 1) //葫芦
    {
        return 7;
    }
    if (checkFlush($colors)) //同花
    {
        return 6;
    }
    if (checkStraight($nums)) //顺子
    {
        return 5;
    }
    if (checkSame($nums, 3) == 1) //三条
    {
        return 4;
    }
    if (checkSame($nums, 2) == 2) //两对
    {
        return 3;
    }
    if (checkSame($nums, 2) == 1) //一对
    {
        return 2;
    }
    //高牌
    return 1;
}

function equalCardType($type, $card1, $card2)
{
    $nums1 = getNums($card1);
    $nums2 = getNums($card2);
    switch ($type) {
        case 10:
            return 0;
        case 5:
        case 9:
            if ($nums1[0] == $nums2[0]) {
                return 0;
            } else {
                if ($nums2[4] == 'A') {
                    return -1;
                }if ($nums1[4] == 'A') {
                    return 1;
                } elseif ($nums2[4] > $nums1[4]) {
                    return 1;
                } elseif ($nums2[4] < $nums1[4]) {
                    return -1;
                }
            }
        case 4: //三条
            $same = 3;
        case 8: //四条
            $same = 4;
        case 7: //葫芦
            $same            = 3;
            $cards1SameCount = $cards2SameCount = [];
            checkSame($nums1, $same, $cards1SameCount);
            checkSame($nums2, $same, $cards2SameCount);
            $_temp1 = array_flip($cards1SameCount);
            $_temp2 = array_flip($cards2SameCount);
            return $_temp1[$same] < $_temp2[$same] ? 1 : -1;
        case 6: //同花
        case 1: //高牌
            if ($nums2[4] > $nums1[4]) {
                return 1;
            } elseif ($nums2[4] > $nums1[4]) {
                return -1;
            } else {
                return 0;
            }
        case 3:
            $same            = 3;
            $cards1SameCount = $cards2SameCount = [];
            checkSame($nums1, $same, $cards1SameCount);
            checkSame($nums2, $same, $cards2SameCount);
            $two1 = $two2 = [];
            $one1 = $one2 = [];
            foreach ($cards1SameCount as $key1 => $value1) {
                if ($value1 == 1) {
                    $one1[] = $key1;
                } elseif ($value1 == 2) {
                    $two1[] = $key1;
                }
            }
            foreach ($cards2SameCount as $key2 => $value2) {
                if ($value2 == 1) {
                    $one2[] = $key2;
                } elseif ($value2 == 2) {
                    $two2[] = $key2;
                }
            }
            //先比较2对的大小
            if (0 == $compare2Res = compareNumber($two1, $two2)) {
                //如果相同，再比较单牌的大小
                return compareNumber($one1, $one2);
            }
            return $compare2Res;
        case 2:
            $same            = 2;
            $cards1SameCount = $cards2SameCount = [];
            checkSame($nums1, $same, $cards1SameCount);
            checkSame($nums2, $same, $cards2SameCount);
            $_temp1 = array_flip($cards1SameCount);
            $_temp2 = array_flip($cards2SameCount);
            if ($_temp1[$same] == $_temp2[$same]) {
                return compareNumber(array_unique($nums1), array_unique($nums2));
            }
            return $_temp1[$same] < $_temp2[$same] ? 1 : -1;

    }

}

function getNums($card)
{
    $nums = [];
    foreach ($card as $k => $v) {
        $nums[] = $v[1];
    }
    sort($nums);
    return $nums;
}

function getColors($card)
{
    $colors = [];
    foreach ($card as $k => $v) {
        $colors[] = $v[0];
    }
    return $colors;
}
//检查是否是同一花色
function checkFlush($color)
{
    return count(array_unique($color)) == 1;
}

//检查是否是顺子
function checkStraight($nums)
{
    if ($nums == [2, 3, 4, 5, 14]) {
        return true;
    }
    $isStraight = true;
    foreach ($nums as $key => $num) {
        $nextKey = $key + 1;
        if (isset($nums[$nextKey]) && ($nums[$nextKey] - $num != 1)) {
            $isStraight = false;
            break;
        }
    }
    return $isStraight;
}

function checkSame(array $nums, $same = 4, array &$sameCounts = [])
{
    // 桶方法
    foreach ($nums as $num) {
        if (!isset($sameCounts[$num])) {
            $sameCounts[$num] = 1;
        } else {
            $sameCounts[$num]++;
        }
    }
    $sameNumber = 0;
    foreach ($sameCounts as $key => $sameCount) {
        if ($sameCount == $same) {
            $sameNumber += 1;
        }
    }
    return $sameNumber;
}

function compareNumber(array $nums1, array $nums2)
{
    rsort($nums1);
    rsort($nums2);
    foreach ($nums2 as $k => $v) {
        if ($v > $nums1[$k]) {
            return 1;
        } elseif ($v < $nums1[$k]) {
            return -1;
        }
    }
    return 0;
}
