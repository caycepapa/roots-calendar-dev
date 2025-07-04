"use strict";

import * as create_modal_window from "./create-modal-window";

export function createFunc() {
    const enabledPostTypes = typeof rc_calendar_settings !== 'undefined' ? rc_calendar_settings.enabled_post_types : ['events'];
    const bodyClasses = document.body.className;
    let currentPostType = 'events'; // デフォルト

    if (bodyClasses.match(/post-type-([a-zA-Z0-9_-]+)/)) {
        currentPostType = RegExp.$1;
    }

    var calBox = document.getElementsByName('cal_box')[0];
    const week = ["日", "月", "火", "水", "木", "金", "土"];
    const today = new Date();
    var showNum = 12;

    if (calBox) {
        showProcess(today);

        function showProcess(date) {
            for (let i = 0; i < showNum; i++) {
                var year = date.getFullYear();
                var month = date.getMonth();

                var calBoxInner = document.createElement('div');
                calBoxInner.className = 'rc-calbox';

                if (month + i + 1 <= 12) {
                    month = month + i;
                } else {
                    month = month + i - 12;
                    year = year + 1;
                }

                calBoxInner.innerHTML = '<div class="rc-calbox__header"><h2 class="rc-calbox__ttl">' + year + "年 " + (month + 1) + "月" + '</h2><label><input type="checkbox" name="calAllChangeCheckboxMonth" value="allCheckFlg">この月をすべて選択/解除</label></div>';

                var calBoxTable = document.createElement('table');
                calBoxTable.innerHTML += createProcess(year, month);
                calBoxInner.appendChild(calBoxTable);
                calBox.appendChild(calBoxInner);
            }
        }

        function createProcess(year, month) {
            if (typeof setting_records_array !== 'undefined') {
                let rc_statelist = setting_records_array;
                var calendar = "<tr class='dayOfWeek'>";

                for (var i = 0; i < week.length; i++) {
                    calendar += "<th>" + week[i] + "<br><input type='checkbox' class='weekday-toggle' data-weekday='" + i + "'></th>";
                }

                calendar += "</tr>";

                var count = 0;
                var startDayOfWeek = new Date(year, month, 1).getDay();
                var endDate = new Date(year, month + 1, 0).getDate();
                var lastMonthEndDate = new Date(year, month, 0).getDate();
                var row = Math.ceil((startDayOfWeek + endDate) / week.length);

                var buttonDomCreate = function(year, month, day) {
                    var events_name = 'rc_events_' + year + "-" + month + "-" + day;
                    return "<a class='rc-addbtn' data-date='" + year + "-" + month + "-" + day + "' data-meta=" + events_name + ">+</a><input type='checkbox' name='allset'>";
                }

                //デフォルトステータスの補完
                var selectCreate = function(rc_statelist, year, month, day) {
                    let option_list = '<option value="">--</option>';
                    let status_name = 'rc_status_' + year + "-" + month + "-" + day;
                    let target_date = rc_status_array.find((v) => v.meta_key === status_name);

                    //デフォルトステータスを取得（未設定時に使用）
                    let default_state_name = '';
                    if (!target_date && typeof rc_default_statuses !== 'undefined' && rc_default_statuses.length > 0) {
                        default_state_name = rc_default_statuses[0].state_name; // 最初の状態をデフォルトに
                    }

                    for (let i = 0; i < rc_statelist.length; i++) {
                        let selected_txt = '';
                        if (target_date) {
                            selected_txt = (target_date.meta_value == rc_statelist[i].state_name) ? 'selected' : '';
                        } else if (rc_statelist[i].state_name === default_state_name) {
                            selected_txt = 'selected';
                        }
                        option_list += '<option value="' + rc_statelist[i].state_name + '"' + selected_txt + '>' + rc_statelist[i].state_name + '</option>';
                    }

                    return "<select class='rc_status_selectbtn' name='" + status_name + "'>" + option_list + "</select>";
                }

                var eventCreate = function(year, month, day) {
                    var events_name = 'rc_events_' + year + "-" + month + "-" + day;
                    var target_date = rc_events_array.find((v) => v.meta_key === events_name);
                    var events_list = '';

                    if (target_date) {
                        var events_list_arry = JSON.parse(target_date.meta_value);
                        for (let i = 0; i < Object.keys(events_list_arry).length; i++) {
                            if (events_list_arry[i].event_name !== '') {
                                events_list += '<a class="rc-event__btn" data-date="' + year + '-' + month + '-' + day + '" data-meta=' + events_name + ' data-eventnum=' + i + '>' + events_list_arry[i].event_name + '</a>';
                            }
                        }
                    }

                    return events_list;
                }

                for (var i = 0; i < row; i++) {
                    calendar += "<tr>";
                    for (var j = 0; j < week.length; j++) {
                        if (i == 0 && j < startDayOfWeek) {
                            calendar += "<td class='disabled'>" + (lastMonthEndDate - startDayOfWeek + j + 1) + "</td>";
                        } else if (count >= endDate) {
                            count++;
                            var counta = count - endDate;
                            counta = counta.toString().padStart(2, '0');
                            calendar += "<td class='disabled'><span>" + counta + "</span></td>";
                        } else {
                            count++;
                            var counta = count.toString().padStart(2, '0');
                            var montha = (month + 1).toString().padStart(2, '0');
                            let dayContent = "<span>" + count + "</span>" + selectCreate(rc_statelist, year, montha, counta) + eventCreate(year, montha, counta) + buttonDomCreate(year, montha, counta);
                            let bg = (year == today.getFullYear() && month == today.getMonth() && count == today.getDate()) ? " style='background:#ffdddd;'" : "";
                            calendar += "<td data-weekday='" + j + "'" + bg + ">" + dayContent + "</td>";
                        }
                    }
                    calendar += "</tr>";
                }

                return calendar;
            }
        }

        function rcBtnAction() {
            var rc_addbtn = document.getElementsByClassName('rc-addbtn');
            if (rc_addbtn.length !== 0) {
                for (let i = 0; i < rc_addbtn.length; i++) {
                    rc_addbtn[i].addEventListener('click', function () {
                        let date = this.dataset.date;
                        let meta = this.dataset.meta;
                        create_modal_window.open(date, meta);
                    });
                }

                var rc_event__btn = document.getElementsByClassName('rc-event__btn');
                for (let i = 0; i < rc_event__btn.length; i++) {
                    rc_event__btn[i].addEventListener('click', function () {
                        let date = this.dataset.date;
                        let meta = this.dataset.meta;
                        let num = this.dataset.eventnum;
                        create_modal_window.open(date, meta, num);
                    });
                }
            }
        }

        function setupWeekdayToggles() {
            const toggles = document.querySelectorAll('.weekday-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', () => {
                    const weekday = toggle.dataset.weekday;
                    const rcCalBox = toggle.closest('.rc-calbox');
                    if (!rcCalBox) return;

                    const rows = rcCalBox.querySelectorAll('table tr');
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length === 7) {
                            const cell = cells[weekday];
                            if (cell) {
                                const checkbox = cell.querySelector('input[name="allset"]');
                                if (checkbox) {
                                    checkbox.checked = toggle.checked;
                                }
                            }
                        }
                    });
                });
            });
        }

        function setupGlobalWeekdayToggles() {
            const globalToggles = document.querySelectorAll('.weekday-toggle-global');
            globalToggles.forEach(toggle => {
                toggle.addEventListener('change', () => {
                    const weekday = toggle.dataset.weekday;
                    const rcCalBoxes = document.querySelectorAll('.rc-calbox');
                    rcCalBoxes.forEach(rcCalBox => {
                        const rows = rcCalBox.querySelectorAll('table tr');
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('td');
                            if (cells.length === 7) {
                                const cell = cells[weekday];
                                if (cell) {
                                    const checkbox = cell.querySelector('input[name="allset"]');
                                    if (checkbox) {
                                        checkbox.checked = toggle.checked;
                                    }
                                }
                            }
                        });
                    });
                });
            });
        }

        rcBtnAction();
        setupWeekdayToggles();
        setupGlobalWeekdayToggles();
    }
}
