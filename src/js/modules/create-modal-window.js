"use strict";

export function open(date,meta,num){

    var cal_container = document.getElementsByName('cal_container')[0];

    if(!cal_container){
        console.error('カレンダーのコンテナが見つかりません。');
        return;
    }

    var eventDomContent = '';

    if(typeof rc_events_array !== 'undefined'){
        var target_events = rc_events_array.find((v) => v.meta_key === meta);
    }

    var events_list_arry;

    var events_posts_array = [];
    var eventDomContentOptions = "";
    if(typeof rc_events_posts_array !== 'undefined'){
        for(let i = 0; i < rc_events_posts_array.length; i++){
            events_posts_array.push({
                id: rc_events_posts_array[i].ID,
                title: rc_events_posts_array[i].post_title
            });
            eventDomContentOptions += "<option value='" + rc_events_posts_array[i].ID + "'>" + rc_events_posts_array[i].post_title + "</option>";
        }
    }

    var addView = (date,meta,num) =>{
        eventDomContent += 
            "<div class='rc-modal__wrap'>" +
                "<p class='rc-modal__ttl'>"+date+"</p>" +
                "<select class='link_type js-input-selectbtn'>" +
                "<option value='url'>URL</option>" +
                "<option value='posts'>投稿から選択</option>" +
                "</select>" +
                "<div class='js-input-target' data-type='url'>" +
                    "<table class='rc-modal__inner'>" +
                        "<tr>" +
                        "<th>タイトル</th>" +
                        "<td><input type='text' name='"+ meta +"["+num+"][event_name]' value=''></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<th>URL</th>" +
                        "<td><input type='text' name='"+ meta +"["+num+"][event_url]' value=''></td>" +
                        "</tr>" +
                        "<tr>" +
                        "<th>カラー</th>" +
                        "<td><input type='color' name='"+ meta +"["+num+"][event_color]' value='#b1fcb0'></td>" +
                        "</tr>" +
                    "</table>" +
                "</div>" +
                "<div class='js-input-target' data-type='posts' style='display:none;'>" +
                    "<table class='rc-modal__inner'>" +
                        "<tr>" +
                        "<th>イベント</th>" +
                        "<td>" +
                            "<select name='"+ meta +"["+num+"][event_id]' class='rc-modal__select'>" +
                                eventDomContentOptions +
                            "</select>" +
                        "</td>" +
                        "</tr>" +
                        "<tr>" +
                        "<th>カラー</th>" +
                        "<td><input type='color' name='"+ meta +"["+num+"][event_color]' value='#b1fcb0'></td>" +
                        "</tr>" +
                    "</table>" +
                "</div>" +
                "<input type='hidden' class='js-input-type' name='" + meta + "["+num+"][event_type]' value=''>" +
                "<div class='rc-modal__submit'><input type='submit' value='追加する' name='save'></div>" + 
                "<a class='rc-modal__close'>閉じる</a>" +
            "</div>";
    }
    
    var rewriteView = (date,meta,num) =>{
        
        if(events_list_arry[num].event_type === 'url'){

            eventDomContent += 
                "<div class='rc-modal__wrap'>" +
                    "<p class='rc-modal__ttl'>"+date+"</p>" +
                    "<table class='rc-modal__inner'>" +
                    "<tr>" +
                    "<th>タイトル</th>" +
                    "<td><input type='text' name='" + meta + "["+num+"][event_name]' value='"+ events_list_arry[num].event_name + "'></td>" +
                    "</tr>" +
                    "<tr>" +
                    "<th>URL</th>" +
                    "<td><input type='text' name='" + meta + "["+num+"][event_url]' value='" + events_list_arry[num].event_url + "'></td>" +
                    "</tr>" +
                    "<tr>" +
                    "<th>カラー</th>" +
                    "<td><input type='color' name='" + meta + "["+num+"][event_color]' value='" + events_list_arry[num].event_color + "'></td>" +
                    "</tr>" +
                    "</table>" +
                    "<div class='rc-modal__submit'><input type='submit' value='更新する' name='save'></div>" + 
                    "<div class='rc-modal__controller'>" +
                        "<a class='rc-modal__delete'>削除</a>" + 
                    "</div>" +
                    "<a class='rc-modal__close'>閉じる</a>" +
                "</div>";

        }else{
            eventDomContent += 
                "<div class='rc-modal__wrap'>" +
                    "<p class='rc-modal__ttl'>"+date+"</p>" +
                    "<table class='rc-modal__inner'>" +
                    "<tr>" +
                    "<th>イベント</th>" +
                    "<td>" +
                        "<select name='" + meta + "["+num+"][event_id]' class='rc-modal__select'>" +
                            eventDomContentOptions +
                        "</select>" +
                    "</td>" +
                    "</tr>" +
                    "<tr>" +
                    "<th>カラー</th>" +
                    "<td>" +
                        "<input type='color' name='" + meta + "["+num+"][event_color]' value='" + events_list_arry[num].event_color + "'>" +
                    "</td>" +
                    "</tr>" +
                    "</table>" +
                    "<div class='rc-modal__submit'><input type='submit' value='更新する' name='save'></div>" +
                    "<div class='rc-modal__controller'>" +
                        "<a class='rc-modal__delete'>削除</a>" + 
                    "</div>" +
                    "<a class='rc-modal__close'>閉じる</a>" +
                "</div>";
        }
    }

    
    if(target_events){
        events_list_arry = JSON.parse(target_events.meta_value);

        // numで追加か更新か変別
        if(num){
            // データがある場合(イベントのリストをクリック時)
            rewriteView(date,meta,num);
        }else{
            var eventNum = Object.keys(events_list_arry).length;
            addView(date,meta,eventNum);
        }

        // 上書き用に準備
        for(let i = 0; i < Object.keys(events_list_arry).length; i++){
            num = Number(num);
    
            if(i !== num){
                eventDomContent +=
                    "<input type='hidden' name='" + meta + "["+i+"]" +"[event_type]' value='" + events_list_arry[i].event_type + "'>";
                eventDomContent +=
                    "<input type='hidden' name='" + meta + "["+i+"]" +"[event_color]' value='" + events_list_arry[i].event_color + "'>";
                eventDomContent +=
                        "<input type='hidden' name='" + meta + "["+i+"]" +"[event_name]' value='" + events_list_arry[i].event_name + "'>";
                if(events_list_arry[i].event_type === 'url'){
                    eventDomContent +=
                        "<input type='hidden' name='" + meta + "["+i+"]" +"[event_url]' value='" + events_list_arry[i].event_url + "'>";
                }else{
                    eventDomContent +=
                        "<input type='hidden' name='" + meta + "["+i+"]" +"[event_id]' value='" + events_list_arry[i].event_id + "'>";
                }
            }
        }
    }else{
        // データがない場合（追加）
        addView(date,meta,0);
    }

    return createModalWindow(eventDomContent, cal_container, meta, num);
}

function createModalWindow(eventDomContent, cal_container, meta, num){

    var eventDomWrap = document.createElement('div');
    eventDomWrap.className = 'rc-cal__inputwrap';
    eventDomWrap.innerHTML += eventDomContent;
    cal_container.appendChild(eventDomWrap);

    var inputSelectBtn = document.querySelector('.js-input-selectbtn');
    var inputType = document.querySelector('.js-input-type');

    // 初期表示
    if(inputSelectBtn){
        var targetType = inputSelectBtn.value;
        var targets = document.querySelectorAll('.js-input-target');
        for(var i = 0; i<targets.length; i++){
            targets[i].style.display = 'none';
        }
        if(targets){
            var target = document.querySelector('.js-input-target[data-type="'+targetType+'"]');
            if(target){
                target.style.display = 'block';
                inputType.value = targetType;
            }
        }
    }

    inputSelectBtn.addEventListener('change', function(){
        var targetType = this.value;
        var targets = document.querySelectorAll('.js-input-target');
        
        
        for(var i = 0; i<targets.length; i++){
            targets[i].style.display = 'none';
        }

        if(targets){
            var target = document.querySelector('.js-input-target[data-type="'+targetType+'"]');
            
            if(target){
                target.style.display = 'block';
                inputType.value = targetType;
            }
        }
    });

    // close
    var rcModalClose = document.getElementsByClassName('rc-modal__close')[0];
    rcModalClose.addEventListener('click', function(){
        close();
    })

    // delete
    var rcModalDelete = document.getElementsByClassName('rc-modal__delete')[0];
    if(rcModalDelete){
        rcModalDelete.addEventListener('click', function(){
            inputDelete(meta,num);
        });
    }
}


export function close(){
    var rc_cal_inputwrap = document.getElementsByClassName('rc-cal__inputwrap')[0];
    if(rc_cal_inputwrap){
        rc_cal_inputwrap.remove();
    }
}

export function inputDelete(meta,num){
    var metaName = meta +"["+num+"][event_name]";
    console.log(metaName);
    var inputName = document.getElementsByName(metaName)[0];

    var metaUrl = meta +"["+num+"][event_url]";
    var inputUrl = document.getElementsByName(metaUrl)[0];

    var metaColor = meta +"["+num+"][event_color]";
    var inputColor = document.getElementsByName(metaColor)[0];

    inputName.value = '';
    inputUrl.value = '';
    inputColor.value = '';

    document.post.submit();
}