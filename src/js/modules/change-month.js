"use strict";

export default function(){

    var rcPrevBtn = document.getElementsByName('rcPrevBtn');
    for(let i = 0; i < rcPrevBtn.length; i++){
        rcPrevBtn[i].addEventListener('click',function(){
            if(rcPrevBtn[i].classList.contains('is-blank')){
                return false;
            }else{
                show_table(rcPrevBtn[i],'prev');
            }
        });
    }

    var rcNextBtn = document.getElementsByName('rcNextBtn');
    for(let i = 0; i < rcNextBtn.length; i++){
        rcNextBtn[i].addEventListener('click',function(){
            if(rcNextBtn[i].classList.contains('is-blank')){
                return false;
            }else{
                show_table(rcNextBtn[i],'next');
            }
        });
    }

    var show_table = (btnElem,flg) => {

        for(let i = 0; i < rcPrevBtn.length; i++){
            rcPrevBtn[i].classList.remove('is-blank');
        }

        for(let i = 0; i < rcNextBtn.length; i++){
            rcNextBtn[i].classList.remove('is-blank');
        }
        
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
            tables[tableIndex - 1].style.display = 'table';
            tables[tableIndex - 1].classList.add('is-current');
        }else if(flg == 'next'){
            tables[tableIndex + 1].style.display = 'table';
            tables[tableIndex + 1].classList.add('is-current');
        }else{
            
        }

        var nextTable = rcWrap.querySelector('.is-current');
        
        if(nextTable.classList.contains('rc-calendar__table--first')){
            for(let i = 0; i < rcPrevBtn.length; i++){
                rcPrevBtn[i].classList.add('is-blank');
            }
        }
        if(nextTable.classList.contains('rc-calendar__table--last')){
            for(let i = 0; i < rcPrevBtn.length; i++){
                rcNextBtn[i].classList.add('is-blank');
            }
        }

        var targetLabel = nextTable.nextElementSibling;

        set_ttl(targetLabel.innerText);
    }

    var set_ttl = (ttlTxt) =>{
        var ttl_dom = document.getElementsByName('rcCalendarMonthTtl');
        for(i = 0; i < ttl_dom.length; i++){
            ttl_dom[i].innerText = ttlTxt;
        }
    }

}