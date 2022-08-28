import { extend } from "flarum/common/extend";
import app from "flarum/forum/app";
import PostStream from "flarum/forum/components/PostStream";
import ReplyComposer from "flarum/forum/components/ReplyComposer";

const getFirstPost = () => {
    return app.current.get("discussion").posts().shift();
};

const refresh = () => {
    const firstPost = getFirstPost();
    const id = firstPost.id();
    const oldHtml = firstPost.contentHtml();

    const interval = setInterval(() => {
        app.store.find("posts", id).then((r) => {
            const newHtml = r.contentHtml();

            if (newHtml !== oldHtml) {
                clearInterval(interval);
                m.redraw();
            }
        });
    }, 500);
};

app.initializers.add("nearata-dsts", () => {
    extend(PostStream.prototype, "oncreate", function () {
        const item = this.element.querySelector(
            ".PostStream > .PostStream-item"
        );
        const btn = item.querySelector(".item-like > button");

        if (btn === null) {
            return;
        }

        btn.addEventListener("click", () => {
            refresh();
        });
    });

    extend(ReplyComposer.prototype, "onsubmit", function () {
        const firstPost = getFirstPost();
        const oldHtml = firstPost.contentHtml();

        if (!oldHtml.startsWith('<p class="Nearata-dsts">')) {
            return;
        }

        refresh();
    });
});
