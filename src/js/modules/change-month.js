"use strict";

export default function(){
    // 各カレンダーを囲むコンテナを選択
    var calendarContainers = document.querySelectorAll('.rc-calendar__wrap'); // '.rc-calendar-container' は各カレンダーコンテナのクラス名と仮定

    calendarContainers.forEach(function(container) {
        var rcPrevBtn = container.querySelectorAll('[name="rcPrevBtn"]');
        var rcNextBtn = container.querySelectorAll('[name="rcNextBtn"]');

        rcPrevBtn.forEach(function(btn) {
            btn.addEventListener('click', function() {
                if(!btn.classList.contains('is-blank')){
                    show_table(btn, 'prev', container);
                }
            });
        });

        rcNextBtn.forEach(function(btn) {
            btn.addEventListener('click', function() {
                if(!btn.classList.contains('is-blank')){
                    show_table(btn, 'next', container);
                }
            });
        });
    });

    var show_table = (btnElem, flg, container) => {
        var rcPrevBtn = container.querySelectorAll('[name="rcPrevBtn"]');
        var rcNextBtn = container.querySelectorAll('[name="rcNextBtn"]');

        // 全てのボタンの 'is-blank' クラスを削除
        rcPrevBtn.forEach(btn => btn.classList.remove('is-blank'));
        rcNextBtn.forEach(btn => btn.classList.remove('is-blank'));

        var rcHeader = btnElem.parentElement;
        var rcWrap = rcHeader.parentElement;

        var tables = rcWrap.querySelectorAll('.rc-calendar__table');
        var currentTable = rcWrap.querySelector('.is-current');
        
        var tablesArray = [].slice.call(tables);
        var tableIndex = tablesArray.indexOf(currentTable);
        
        for(let i = 0; i < tables.length; i++){
            tables[i].style.display = 'none';
            tables[i].classList.remove('is-current');
        }

        if(flg == 'prev'){
            tables[tableIndex - 1].style.display = 'table';
            tables[tableIndex - 1].classList.add('is-current');
        } else if(flg == 'next'){
            tables[tableIndex + 1].style.display = 'table';
            tables[tableIndex + 1].classList.add('is-current');
        }

        var nextTable = rcWrap.querySelector('.is-current');
        
        if(nextTable.classList.contains('rc-calendar__table--first')){
            rcPrevBtn.forEach(btn => btn.classList.add('is-blank'));
        }
        if(nextTable.classList.contains('rc-calendar__table--last')){
            rcNextBtn.forEach(btn => btn.classList.add('is-blank'));
        }

        var targetLabel = nextTable.nextElementSibling;
        set_ttl(targetLabel.innerText, container);
    }

    var set_ttl = (ttlTxt, container) => {
        var ttl_dom = container.querySelectorAll('[name="rcCalendarMonthTtl"]');
        ttl_dom.forEach(dom => dom.innerText = ttlTxt);
    }
}