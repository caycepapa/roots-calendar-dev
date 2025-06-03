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
        eventDomContent += `
            <div class='rc-modal__wrap'>
                <p class='rc-modal__ttl'>${date}</p>
                <select class='link_type js-input-selectbtn'>
                    <option value='url'>URL</option>
                    <option value='posts'>投稿から選択</option>
                </select>

                <div class='js-input-target' data-type='url'>
                    <table class='rc-modal__inner'>
                        <tr>
                            <th>タイトル</th>
                            <td><input class='js-event-name-input' type='text' name='${meta}[${num}][event_name]' value=''></td>
                        </tr>
                        <tr>
                            <th>URL</th>
                            <td><input type='text' name='${meta}[${num}][event_url]' value=''></td>
                        </tr>
                        <tr>
                            <th>カラー</th>
                            <td><input type='color' name='${meta}[${num}][event_color]' value='#b1fcb0'></td>
                        </tr>
                    </table>
                    <input type='hidden' name='${meta}[${num}][event_type]' value='url'>
                </div>

                <div class='js-input-target' data-type='posts' style='display:none;'>
                    <table class='rc-modal__inner'>
                        <tr>
                            <th>イベント</th>
                            <td>
                                <select name='${meta}[${num}][event_id]' class='rc-modal__select js-event-name-select'>
                                    ${eventDomContentOptions}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>カラー</th>
                            <td><input type='color' name='${meta}[${num}][event_color]' value='#b1fcb0'></td>
                        </tr>
                    </table>
                    <input type='hidden' class='js-rc-post-name' name='${meta}[${num}][event_name]' value=''>
                    <input type='hidden' name='${meta}[${num}][event_type]' value='post'>
                </div>
                <div class='rc-modal__submit'><input type='submit' value='追加する' name='save'></div>
                <a class='rc-modal__close'>閉じる</a>
            </div>
        `;
    }
    
    var rewriteView = (date,meta,num) =>{
        
        const event = events_list_arry[num];

        console.log(event);

        const headContent = `
            <div class='rc-modal__wrap'>
                <p class='rc-modal__ttl'>${date}</p>
                <table class='rc-modal__inner'>
        `;

        let specificContent = '';

        if (event.event_type === 'url') {
            specificContent = `
                <tr>
                    <th>タイトル</th>
                    <td><input type='text' name='${meta}[${num}][event_name]' value='${event.event_name}'></td>
                </tr>
                <tr>
                    <th>URL</th>
                    <td><input type='text' name='${meta}[${num}][event_url]' value='${event.event_url}'></td>
                </tr>
                <tr>
                    <th>カラー</th>
                    <td><input type='color' name='${meta}[${num}][event_color]' value='${event.event_color}'></td>
                </tr>
                <input type='hidden' name='${meta}[${num}][event_type]' value='url'>
            `;
        } else {
            specificContent = `
                <tr>
                    <th>イベント</th>
                    <td>
                        <select name='${meta}[${num}][event_id]' class='rc-modal__select'>
                            ${eventDomContentOptions}
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>カラー</th>
                    <td>
                        <input type='color' name='${meta}[${num}][event_color]' value='${event.event_color}'>
                    </td>
                </tr>
                <input type='hidden' class='js-rc-post-name' name='${meta}[${num}][event_name]' value='${event.event_name}'>
                <input type='hidden' name='${meta}[${num}][event_type]' value='post'>
            `;
        }

        const footerContent = `
                </table>
                <div class='rc-modal__submit'><input type='submit' value='更新する' name='save'></div>
                <div class='rc-modal__controller'><a class='rc-modal__delete'>削除</a></div>
                <a class='rc-modal__close'>閉じる</a>
            </div>
        `;

        eventDomContent += headContent + specificContent + footerContent;
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

    // 初期表示のinputのtypeを設定
    if(inputSelectBtn){
        var targetType = inputSelectBtn.value;
        changeInputType(targetType);

        // イベントの選択
        inputSelectBtn.addEventListener('change', function(){
            var targetType = this.value;
            changeInputType(targetType);
        });
    }


    /*-----------------------------------------
    イベント名のセレクトボックスの変更イベント
    選択されたイベント名を入力フィールドに設定
    -----------------------------------------*/
    var eventNameSelect = document.querySelector('.js-event-name-select');
    if(eventNameSelect){
        // 初期値を設定
        var initialOption = eventNameSelect.options[eventNameSelect.selectedIndex];
        var postNameInput = document.querySelector('.js-rc-post-name');
        if(postNameInput){
            postNameInput.value = initialOption.textContent;
        }
        // セレクトボックスの変更イベントを設定
        eventNameSelect.addEventListener('change', function(){
            var selectedOption = this.options[this.selectedIndex];
            var postNameInput = document.querySelector('.js-rc-post-name');
            postNameInput.value = selectedOption.textContent;
        });
    }

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

/*-----------------------------------------
inputのtypeを変更する関数
引数：targetType
urlまたはposts
urlの場合はURL入力、postsの場合は投稿選択のinputを表示
引数の値に応じて、他のinputはdisableにする
-----------------------------------------*/
function changeInputType(targetType){
    var targets = document.querySelectorAll('.js-input-target');

    // 全てのターゲットを非表示にし、入力を無効化
    for(var i = 0; i<targets.length; i++){
        targets[i].style.display = 'none';
        var inputs = targets[i].querySelectorAll('input, select');
        for(var j = 0; j<inputs.length; j++){
            inputs[j].disabled = true;
        }
    }

    // 選択されたターゲットのみ表示し、入力を有効化
    if(targets){
        var target = document.querySelector('.js-input-target[data-type="'+targetType+'"]');
        if(target){
            target.style.display = 'block';
            var inputs = target.querySelectorAll('input, select');
            for(var j = 0; j<inputs.length; j++){
                inputs[j].disabled = false;
            }
        }
    }
}


function close(){
    var rc_cal_inputwrap = document.getElementsByClassName('rc-cal__inputwrap')[0];
    if(rc_cal_inputwrap){
        rc_cal_inputwrap.remove();
    }
}

function inputDelete(meta,num){

    var metaType = meta +"["+num+"][event_type]";
    var inputType = document.getElementsByName(metaType)[0];

    if(inputType.value == 'url'){
        var metaName = meta +"["+num+"][event_name]";
        var inputName = document.getElementsByName(metaName)[0];

        var metaUrl = meta +"["+num+"][event_url]";
        var inputUrl = document.getElementsByName(metaUrl)[0];

        var metaColor = meta +"["+num+"][event_color]";
        var inputColor = document.getElementsByName(metaColor)[0];

        inputName.value = '';
        inputUrl.value = '';
        inputColor.value = '';
    }else{
        var metaName = meta +"["+num+"][event_name]";
        var inputName = document.getElementsByName(metaName)[0];

        var metaId = meta +"["+num+"][event_id]";
        var inputId = document.getElementsByName(metaId)[0];

        var metaColor = meta +"["+num+"][event_color]";
        var inputColor = document.getElementsByName(metaColor)[0];

        inputName.value = '';
        inputId.value = '';
        inputColor.value = '';
        inputType.value = '';
    }

    document.post.submit();
}