"use strict";

export function createFunc(){

    var calBox = document.getElementsByName('calBox')[0];

    const week = ["日", "月", "火", "水", "木", "金", "土"];
    const today = new Date();
    var showNum = 12;

    showProcess(today);

    /*-----------------------------------------
    年間カレンダー作成
    -----------------------------------------*/
    function showProcess(date) {
        var year = date.getFullYear();
        var month = date.getMonth();

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
    }

    /*-----------------------------------------
    state 取得
    -----------------------------------------*/
    function stateSet(){
        let rc_statelist = document.getElementsByName('rc_statelist')[0];
        let rc_statelist_txt = [];
        for(let i = 0; i < rc_statelist.childElementCount; i++){
            rc_statelist_txt.push(rc_statelist.children[i].textContent);
        }
        return rc_statelist_txt;
    }

    /*-----------------------------------------
    カレンダーテーブル生成
    -----------------------------------------*/
    function createProcess(year, month) {

        let rc_statelist = stateSet();

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

        var buttonDomCreate = function(year,month,day){
            return "<a class='rc_addbtn' data-date='" + year + "-" + month + "-" + day + "'>+</a><input type='checkbox' name='allset'>";
        }

        var selectCreate = function(rc_statelist,year,month,day){
            var optionTxt = '';
            for(let i = 0; i < rc_statelist.length; i++){
                optionTxt += '<option value="'+rc_statelist[i]+'">'+rc_statelist[i]+'</option>';
            }
            return "<select name='" + year + "-" + month + "-" + day+"-state'>" + optionTxt + "</select>";
        }

        for (var i = 0; i < row; i++) {
            calendar += "<tr>";
            for (var j = 0; j < week.length; j++) {
                if (i == 0 && j < startDayOfWeek) {
                    calendar += "<td class='disabled'>" + (lastMonthEndDate - startDayOfWeek + j + 1) + "</td>";
                } else if (count >= endDate) {
                    count++;
                    var counta = count - endDate;
                    counta = counta.toString().padStart(2,'0');
                    calendar += "<td class='disabled'>" + "<span>" + count + "</span>" + "</td>";
                } else {
                    count++;
                    if(year == today.getFullYear() && month == (today.getMonth()) && count == today.getDate()){
                        var counta = count;
                        var montha = month + 1;
                        counta = counta.toString().padStart(2,'0');
                        calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
                    }else if(year == today.getFullYear() && month == (today.getMonth()) && count < today.getDate()){
                        var counta = count;
                        counta = counta.toString().padStart(2,'0');
                        calendar += "<td>" + "<span>" + count + "</span>" + "</td>";
                    }else{
                        var counta = count;
                        counta = counta.toString().padStart(2,'0');

                        if(month + 1 <= 12){
                            var montha = month + 1;
                            montha = montha.toString().padStart(2,'0');
                            calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
                        }else{
                            var montha = month + 1 - 12;
                            montha = montha.toString().padStart(2,'0');
                            calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
                        }
                    }
                }
            }
            calendar += "</tr>";
        }

        return calendar;
    }

    // 生成したカレンダーのプラスボタンをクリックイベント
    function rcBtnAction(){
        var rc_addbtn = document.getElementsByClassName('rc_addbtn');

        for(let i = 0; i < rc_addbtn.length; i++){
            rc_addbtn[i].addEventListener('click',function(){
                let date = this.dataset.date;
                createInputView(date);
            })
        }
    }
    rcBtnAction();

    // クリックイベントアクション
    function createInputView(date){
        var calContainer = document.getElementsByName('calContainer')[0];
        var eventDom = "<div><p>"+date+"</p><input type='text'></div>";
        var eventDomContent = document.createElement('div');
        eventDomContent.className = 'rc_cal__inputWrap';
        eventDomContent.innerHTML += eventDom;
        calContainer.appendChild(eventDomContent);
    }
}

export default function(){

}