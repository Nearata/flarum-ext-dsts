(()=>{var t={n:e=>{var o=e&&e.__esModule?()=>e.default:()=>e;return t.d(o,{a:o}),o},d:(e,o)=>{for(var n in o)t.o(o,n)&&!t.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:o[n]})},o:(t,e)=>Object.prototype.hasOwnProperty.call(t,e),r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},e={};(()=>{"use strict";t.r(e);const o=flarum.core.compat["common/extend"],n=flarum.core.compat["forum/app"];var r=t.n(n);const a=flarum.core.compat["forum/components/PostStream"];var c=t.n(a);const s=flarum.core.compat["forum/components/ReplyComposer"];var l=t.n(s),u=function(){return r().current.get("discussion").posts().shift()},i=function(){var t=u(),e=t.id(),o=t.contentHtml(),n=setInterval((function(){r().store.find("posts",e).then((function(t){t.contentHtml()!==o&&(clearInterval(n),m.redraw())}))}),500)};r().initializers.add("nearata-dsts",(function(){(0,o.extend)(c().prototype,"oncreate",(function(){var t=this.element.querySelector(".PostStream > .PostStream-item");if(null!==t){var e=t.querySelector(".item-like > button");null!==e&&e.addEventListener("click",(function(){i()}))}})),(0,o.extend)(l().prototype,"onsubmit",(function(){u().contentHtml().startsWith('<p class="Nearata-dsts">')&&i()}))}))})(),module.exports=e})();
//# sourceMappingURL=forum.js.map