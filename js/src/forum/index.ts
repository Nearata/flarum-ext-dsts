import { ApiResponseSingle } from "flarum/common/Store";
import { extend } from "flarum/common/extend";
import Post from "flarum/common/models/Post";
import app from "flarum/forum/app";
import PostStream from "flarum/forum/components/PostStream";
import ReplyComposer from "flarum/forum/components/ReplyComposer";

const getFirstPost = (): Post => {
    return app.current.get("discussion").posts().shift();
};

const refresh = () => {
    const firstPost = getFirstPost();
    const id = firstPost.id();
    const oldHtml = firstPost.contentHtml();

    if (!id) {
        return;
    }

    const interval = setInterval(() => {
        // @ts-ignore
        app.store.find("posts", id).then<any>((r: ApiResponseSingle<Post>) => {
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

        if (item === null) {
            return;
        }

        const btn = item.querySelector(".item-like > button");

        if (btn === null) {
            return;
        }

        btn.addEventListener("click", () => refresh());
    });

    extend(ReplyComposer.prototype, "onsubmit", function () {
        const oldHtml = getFirstPost().contentHtml();

        if (oldHtml && oldHtml.startsWith('<p class="Nearata-dsts">')) {
            refresh();
        }
    });
});
