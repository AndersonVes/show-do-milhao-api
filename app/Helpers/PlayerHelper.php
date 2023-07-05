<?php

function getDifficulty($questionNo)
{
    if($questionNo < 6)
        return 'easy';

    if($questionNo < 11)
        return 'medium';

    return 'hard';
}

function nextPrize($questionNo) {
    $prizes = [
        1000,
        2000,
        3000,
        4000,
        5000,

        10000,
        20000,
        30000,
        40000,
        50000,

        100000,
        200000,
        300000,
        400000,

        1000000,
    ];

    return $prizes[$questionNo - 1];
}

// O programa consiste em três rodadas e uma pergunta final: a primeira 
    // contém cinco perguntas, cada uma valendo mil reais cumulativos. A segunda, 
    // de cinco perguntas, valendo R$ 10 mil cumulativos cada. A terceira, de cinco 
    // perguntas de R$100 mil reais cumulativos cada. A última pergunta vale R$ 1 milhão.
