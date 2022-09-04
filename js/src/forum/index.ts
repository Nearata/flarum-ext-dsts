import Application from "flarum/common/Application";
import Button from "flarum/common/components/Button";
import TextEditor from "flarum/common/components/TextEditor";
import Tooltip from "flarum/common/components/Tooltip";
import { extend } from "flarum/common/extend";
import app from "flarum/forum/app";

app.initializers.add("nearata-dsts", () => {
    extend(
        Application.prototype,
        "request",
        function (promise, originalOptions) {
            const apiUrl = app.forum.attribute("apiUrl");
            const isPost = originalOptions.url.startsWith(`${apiUrl}/posts`);

            const flag = originalOptions.method === "POST" && isPost;
            const flag1 =
                originalOptions.method === "PATCH" &&
                isPost &&
                ("isHidden" in originalOptions.body.data.attributes ||
                    "isLiked" in originalOptions.body.data.attributes);

            if (flag || flag1) {
                promise.then(() =>
                    app.store.find("posts").then(() => m.redraw())
                );
            }
        }
    );

    extend(TextEditor.prototype, "controlItems", function (items) {
        items.add(
            "nearataDsts",
            m(
                Tooltip,
                {
                    text: app.translator.trans("nearata-dsts.forum.button"),
                },
                [
                    m(Button, {
                        icon: "fas fa-eye-slash",
                        class: "Button Button--icon",
                        onclick: () => {
                            this.attrs.composer.editor.insertAtCursor(
                                '[nearata-dsts login="true" like="false" reply="false"][/nearata-dsts]'
                            );
                        },
                    }),
                ]
            )
        );
    });
});
