"use strict";

export function createFunc(){

    var calBox = document.getElementsByName('calBox')[0];

    const week = ["日", "月", "火", "水", "木", "金", "土"];
    const today = new Date();
    var showDate = new Date(today.getFullYear(), today.getMonth(), 1);
    var currentMonth = 0;

    showProcess(today);

    function showProcess(date) {
        var year = date.getFullYear();
        var month = date.getMonth();
        var showNum = 12;

        for(let i = 0; i <showNum ; i++ ){

            // 1月分のカレンダーwrap
            var calBoxInner = document.createElement('div');
            calBoxInner.className = 'rc_calbox';

            if(month + i + 1 <= 12){
                calBoxInner.innerHTML = '<h6>' + year + "年 " + (month + i + 1) + "月" + '</h6>';
            }else{
                calBoxInner.innerHTML = '<h6>' + (year+1) + "年 " + (month + i + 1 - 12) + "月" + '</h6>';
            }

            var calBoxTable = document.createElement('table');
            calBoxTable.innerHTML += createProcess(year, month + i);

            calBoxInner.appendChild(calBoxTable);
            calBox.appendChild(calBoxInner);
        }

        // calDay = document.getElementsByName('calDay');

        // for(let i = 0; i < calDay.length; i++){
        //     calDay[i].addEventListener('click',function(){
        //         if(calDay[i].classList.contains('is-selected')){
        //             removeDay(calDay[i].dataset.date);
        //             removeInputHidden(calDay[i].dataset.date);
        //         }else{
        //             createDay(calDay[i].dataset.date);
        //             createInputHidden(calDay[i].dataset.date);
        //             createSelected(calDay[i].dataset.date);
        //         }
        //     })
        // }
    }

    function createProcess(year, month) {

        var calendar = "<tr class='dayOfWeek'>";

        for (var i = 0; i < week.length; i++) {
            calendar += "<th>" + week[i] + "</th>";
        }

        calendar += "</tr>";

        var count = 0;
        var startDayOfWeek = new Date(year, month, 1).getDay();
        var endDate = new Date(year, month + 1, 0).getDate();
        var lastMonthEndDate = new Date(year, month, 0).getDate();
        var row = Math.ceil((startDayOfWeek + endDate) / week.length);
        var buttonInnerDom = "<a class='rc_addbtn'>+</a><input type='checkbox' name='allset'>";

        for (var i = 0; i < row; i++) {
            calendar += "<tr>";
            for (var j = 0; j < week.length; j++) {
                if (i == 0 && j < startDayOfWeek) {
                    calendar += "<td class='disabled'>" + (lastMonthEndDate - startDayOfWeek + j + 1) + "</td>";
                } else if (count >= endDate) {
                    count++;
                    var counta = count - endDate;
                    counta = counta.toString().padStart(2,'0');
                    calendar += "<td class='disabled'>" + counta + "</td>";
                } else {
                    count++;
                    if(year == today.getFullYear() && month == (today.getMonth()) && count == today.getDate()){
                        var counta = count;
                        var montha = month + 1;
                        counta = counta.toString().padStart(2,'0');
                        calendar += "<td>" + count + buttonInnerDom + "</td>";
                    }else if(year == today.getFullYear() && month == (today.getMonth()) && count < today.getDate()){
                        var counta = count;
                        counta = counta.toString().padStart(2,'0');
                        calendar += "<td>" + count + "</td>";
                    }else{
                        var counta = count;
                        counta = counta.toString().padStart(2,'0');

                        if(month + 1 <= 12){
                            var montha = month + 1;
                            montha = montha.toString().padStart(2,'0');
                            calendar += "<td>" + count + buttonInnerDom + "</td>";
                        }else{
                            var montha = month + 1 - 12;
                            montha = montha.toString().padStart(2,'0');
                            calendar += "<td>>" + count + buttonInnerDom + "</td>";
                        }
                    }
                }
            }
            calendar += "</tr>";
        }

        return calendar;
    }
}

export default function(){

}