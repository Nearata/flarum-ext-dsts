module.exports=function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}return n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=4)}([,function(t,e){t.exports=flarum.core.compat["common/extend"]},function(t,e){t.exports=flarum.core.compat["forum/components/PostStream"]},function(t,e){t.exports=flarum.core.compat["forum/components/ReplyComposer"]},function(t,e,n){"use strict";n.r(e);var r=n(1),o=n(2),u=n.n(o),c=n(3),i=n.n(c),a=function(){return app.current.get("discussion").posts().shift()},s=function(){var t=a(),e=t.id(),n=t.contentHtml(),r=setInterval((function(){app.store.find("posts",e).then((function(t){t.contentHtml()!==n&&(clearInterval(r),m.redraw())}))}),500)};app.initializers.add("nearata-dsts",(function(){Object(r.extend)(u.a.prototype,"oncreate",(function(){var t=this.element.querySelector(".PostStream > .PostStream-item").querySelector(".item-like > button");null!==t&&t.addEventListener("click",(function(){s()}))})),Object(r.extend)(i.a.prototype,"onsubmit",(function(){a().contentHtml().startsWith('<p class="Nearata-dsts">')&&s()}))}))}]);
//# sourceMappingURL=forum.js.map