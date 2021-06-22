import Vue from 'vue'
import VueI18n from 'vue-i18n'
Vue.use(VueI18n)
import zh from './zh'
import en from './en'
import jp from './jp'
import hk from './hk'
let lang=localStorage.getItem('lang') || '';
if(lang==''){
	lang='zh';
	localStorage.setItem('lang',lang)
}
const i18n=new VueI18n({
	locale:lang,
	messages:{
		zh: zh,
		en: en,
		jp: jp,
		hk: hk
	}
})
export default i18n