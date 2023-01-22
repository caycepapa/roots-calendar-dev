"use strict";

import { forEach } from "lodash";
import * as create_modal_window from "./create-modal-window";

export function createFunc(){

    var calBox = document.getElementsByName('cal_box')[0];

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
            calBoxInner.className = 'rc-calbox';

            if(month + i + 1 <= 12){
                calBoxInner.innerHTML = '<h2>' + year + "年 " + (month + i + 1) + "月" + '</h2>';
            }else{
                calBoxInner.innerHTML = '<h2>' + (year+1) + "年 " + (month + i + 1 - 12) + "月" + '</h2>';
            }

            var calBoxTable = document.createElement('table');
            calBoxTable.innerHTML += createProcess(year, month + i);

            calBoxInner.appendChild(calBoxTable);
            calBox.appendChild(calBoxInner);
        }
    }

    /*-----------------------------------------
    カレンダーテーブル生成
    -----------------------------------------*/
    function createProcess(year, month) {


        let rc_statelist = setting_records_array;

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
            var events_name = 'rc_events_' + year + "-" + month + "-" + day;
            return "<a class='rc-addbtn' data-date='" + year + "-" + month + "-" + day + "' data-meta="+ events_name +">+</a><input type='checkbox' name='allset'>";
        }

        // セレクトボタン生成関数
        var selectCreate = function(rc_statelist,year,month,day){
            let option_list = '<option value="">--</option>';
            // ex) rc_status_2022-02-04
            let status_name = 'rc_status_' + year + "-" + month + "-" + day;
            // rc_data_arrayはcalendar.phpに記載
            let target_date = rc_status_array.find((v) => v.meta_key === status_name);

            for(let i = 0; i < rc_statelist.length; i++){
                let selected_txt = '';
                if(target_date){
                    selected_txt = (target_date.meta_value == rc_statelist[i].state_name)? 'selected' : '';
                }
                option_list += '<option value="'+rc_statelist[i].state_name+'"'+ selected_txt +'>'+rc_statelist[i].state_name+'</option>';
            }
            
            return "<select class='rc_status_selectbtn' name='"+ status_name +"'>" + option_list + "</select>";
        }

        // イベント生成
        var eventCreate = function(year,month,day){
            var events_name = 'rc_events_' + year + "-" + month + "-" + day;
            var target_date = rc_events_array.find((v) => v.meta_key === events_name);
            var events_list = '';

            if(target_date){
                var events_list_arry = JSON.parse(target_date.meta_value);
                for(let i = 0; i < Object.keys(events_list_arry).length; i++){
                    events_list += 
                        '<a class="rc-event__btn" data-date="' +year + '-' + month + '-' + day + '" data-meta='+ events_name +' data-eventnum=' + i + '>' + events_list_arry[i].event_name + '</a>';
                }
            }else{
                events_list = '';
            }

            return events_list;
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
                        calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + eventCreate(year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
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
                            calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + eventCreate(year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
                        }else{
                            // 翌年
                            var montha = month + 1 - 12;
                            montha = montha.toString().padStart(2,'0');
                            calendar += "<td>" + "<span>" + count + "</span>" + selectCreate(rc_statelist,year,montha,counta) + eventCreate(year,montha,counta) + buttonDomCreate(year,montha,counta) + "</td>";
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

        var rc_addbtn = document.getElementsByClassName('rc-addbtn');

        for(let i = 0; i < rc_addbtn.length; i++){
            rc_addbtn[i].addEventListener('click',function(){
                let date = this.dataset.date;
                let meta = this.dataset.meta;
                create_modal_window.open(date,meta);
            })
        }

        var rc_event__btn = document.getElementsByClassName('rc-event__btn');
        for(let i = 0; i < rc_event__btn.length; i++){
            rc_event__btn[i].addEventListener('click',function(){
                let date = this.dataset.date;
                let meta = this.dataset.meta;
                let num = this.dataset.eventnum;
                create_modal_window.open(date,meta,num);
            })
        }
    }

    rcBtnAction();
}

export default function(){

}