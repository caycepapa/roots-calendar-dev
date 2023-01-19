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

        console.log(js_array);

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

        // イベント追加ボタン生成関数
        var buttonDomCreate = function(year,month,day){
            return "<a class='rc_addbtn' data-date='" + year + "-" + month + "-" + day + "'>+</a><input type='checkbox' name='allset'>";
        }

        // セレクトボタン生成関数
        var selectCreate = function(rc_statelist,year,month,day){
            var option_list = '<option value="">--</option>';

            // ex) rc_status_2022-02-04
            var status_name = 'rc_status_' + year + "-" + month + "-" + day;

            // js_arrayはcaalender.phpに記載
            const targetUser = js_array.find((v) => v.meta_key === status_name);
            
            for(let i = 0; i < rc_statelist.length; i++){
                var selected_txt = (targetUser.meta_value == rc_statelist[i])? 'selected' : '';
                option_list += '<option value="'+rc_statelist[i]+'"'+ selected_txt +'>'+rc_statelist[i]+'</option>';
            }
            
            return "<select class='rc_status_selectbtn' name='"+ status_name +"'>" + option_list + "</select>";
        }

        // カレンダー生成
        for (var i = 0; i < row; i++) {
            calendar += "<tr>";
            for (var j = 0; j < week.length; j++) {
                if (i == 0 && j < startDayOfWeek) {
                    // 前月の日付部分生成
                    calendar += "<td class='disabled'>" + (lastMonthEndDate - startDayOfWeek + j + 1) + "</td>";
                } else if (count >= endDate) {
                    // 翌月の日付部分生成
                    count++;
                    var counta = count - endDate;
                    counta = counta.toString().padStart(2,'0');
                    calendar += "<td class='disabled'>" + "<span>" + counta + "</span>" + "</td>";
                } else {
                    count++;
                    if(year == today.getFullYear() && month == (today.getMonth()) && count == today.getDate()){
                        // 当日生成
                        var counta = count;
                        var montha = month + 1;
                        counta = counta.toString().padStart(2,'0');
                        montha = montha.toString().padStart(2,'0');
                        calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
                    }else if(year == today.getFullYear() && month == (today.getMonth()) && count < today.getDate()){
                        // 当月の当日より前の日（過ぎてしまった日）
                        var counta = count;
                        counta = counta.toString().padStart(2,'0');
                        calendar += "<td>" + "<span>" + count + "</span>" + "</td>";
                    }else{
                        var counta = count;
                        counta = counta.toString().padStart(2,'0');
                        if(month + 1 <= 12){
                            // 当日以降
                            var montha = month + 1;
                            montha = montha.toString().padStart(2,'0');
                            calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
                        }else{
                            // 翌年
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

    // 生成したカレンダーのプラスボタンをクリックイベントを設定
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

    // イベント追加　クリック時アクション
    function createInputView(date){
        var calContainer = document.getElementsByName('calContainer')[0];
        var eventDom = "<div><p>"+date+"</p><input type='text' name='rc_date_"+date+"[1][text]'><input type='text' name='rc_date_"+date+"[1][url]'><a class='rc_date_add'>追加</a></div>";
        var eventDomContent = document.createElement('div');
        eventDomContent.className = 'rc_cal__inputWrap';
        eventDomContent.innerHTML += eventDom;
        calContainer.appendChild(eventDomContent);

        var rc_date_add = document.getElementsByClassName('rc_date_add');
        rc_date_add.addEventListener('click',function(){
            // {1{type:'イベント',text:'テキストです',url:'https://roots.run'}}
        });
    }
}

export default function(){

}