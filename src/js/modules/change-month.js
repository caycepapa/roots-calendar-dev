"use strict";

export default function(){

    var rcPrevBtn = document.getElementsByName('rcPrevBtn');
    for(let i = 0; i < rcPrevBtn.length; i++){
        rcPrevBtn[i].addEventListener('click',function(){
            show_table(rcPrevBtn[i],'prev');
        })
    }

    var rcNextBtn = document.getElementsByName('rcNextBtn');
    for(let i = 0; i < rcNextBtn.length; i++){
        rcNextBtn[i].addEventListener('click',function(){
            show_table(rcNextBtn[i],'next');
        })
    }

    var show_table = (btnElem,flg) => {
        
        var rcHeader = btnElem.parentElement;
        var rcWrap = rcHeader.parentElement;

        var tables = rcWrap.querySelectorAll('.rc-calendar__table');
        var currentTable = rcWrap.querySelector('.is-current');
        
        var tablesArray = [].slice.call(tables);
        var tableIndex = tablesArray.indexOf( currentTable );
        
        for(let i = 0; i < tables.length; i++){
            tables[i].style.display = 'none';
            tables[i].classList.remove('is-current');
        }

        if(flg == 'prev'){
            tables[tableIndex - 1].style.display = 'block';
            tables[tableIndex - 1].classList.add('is-current');
        }else if(flg == 'next'){
            tables[tableIndex + 1].style.display = 'block';
            tables[tableIndex + 1].classList.add('is-current');
        }else{
            tables[tableIndex].style.display = 'block';
        }

        var nextTable = rcWrap.querySelector('.is-current');
        var targetLabel = nextTable.nextElementSibling;
        set_ttl(targetLabel.innerText);
    }

    var set_ttl = (ttlTxt) =>{
        var ttl_dom = document.getElementsByName('rcCalendarMonthTtl')[0];
        ttl_dom.innerText = ttlTxt;
    }

    var rcCalendarLabel = document.getElementsByName('rcCalendarLabel')[0];

    set_ttl(rcCalendarLabel.innerText);
}