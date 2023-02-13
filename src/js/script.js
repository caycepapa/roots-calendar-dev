"use strict";

import { gsap } from "gsap";
import { PixiPlugin } from "gsap/PixiPlugin";
import device from './modules/device.min';
import handle from './modules/handle';

/* ///////////////////////
init
/////////////////////// */
handle();
//viewport();

var rc_cal_day = document.getElementsByClassName('rc_cal_day');

var click_action = (dom) =>{
    remove_balloon();
    let rc_cal_day_balloon = dom.querySelectorAll('.rc_cal_balloon')[0];
    rc_cal_day_balloon.style.display = 'block';
}

var remove_balloon = () => {
    let rc_cal_day_balloon_all = document.querySelectorAll('.rc_cal_balloon');

    for(let i = 0; i < rc_cal_day_balloon_all.length; i++){
        rc_cal_day_balloon_all[i].style.display = 'none';
    }
}

for(var i = 0; i < rc_cal_day.length; i++){
    let rc_cal_day_hasevent = rc_cal_day[i].querySelectorAll('.rc_cal_balloon');

    if(rc_cal_day_hasevent.length !== 0){
        rc_cal_day[i].addEventListener('click',function(){
            click_action(this);
        });
    }
}