"use strict";

export function open(date,meta,num){

    var cal_container = document.getElementsByName('cal_container')[0];
    var eventDomContent = '';
    var target_events = rc_events_array.find((v) => v.meta_key === meta);
    var events_list_arry;

    var addView = (date,meta,num) =>{
        eventDomContent += 
            "<div class='rc-modal__wrap'>" +
                "<p class='rc-modal__ttl'>"+date+"</p>" +
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
                    "<td><input type='color' name='"+ meta +"["+num+"][event_color]' value='#FF0000'></td>" +
                    "</tr>" +
                "</table>" +
                "<div class='rc-modal__submit'><input type='submit' value='追加する' name='save'></div>" + 
                "<a class='rc-modal__close'>閉じる</a>" +
            "</div>";
    }

    var rewriteView = (date,meta,num) =>{
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
    }

    if(target_events){
        events_list_arry = JSON.parse(target_events.meta_value);

        // データがある場合
        if(num){
            rewriteView(date,meta,num);
        }else{
            // 最後に追加
            var eventNum = Object.keys(events_list_arry).length;
            addView(date,meta,eventNum);
        }

        for(let i = 0; i < Object.keys(events_list_arry).length; i++){
            num = Number(num);
    
            if(i !== num){
                eventDomContent +=
                    "<input type='hidden' name='" + meta + "["+i+"]" +"[event_name]' value='" + events_list_arry[i].event_name + "'>";
                eventDomContent +=
                    "<input type='hidden' name='" + meta + "["+i+"]" +"[event_url]' value='" + events_list_arry[i].event_url + "'>";
                eventDomContent +=
                    "<input type='hidden' name='" + meta + "["+i+"]" +"[event_color]' value='" + events_list_arry[i].event_color + "'>";
            }
        }

    }else{
        // データがない場合（追加）
        addView(date,meta,0);
    }


    var eventDomWrap = document.createElement('div');
    eventDomWrap.className = 'rc-cal__inputwrap';
    eventDomWrap.innerHTML += eventDomContent;
    cal_container.appendChild(eventDomWrap);

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
    rc_cal_inputwrap.remove();
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