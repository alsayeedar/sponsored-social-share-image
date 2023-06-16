jQuery(document).ready(function($){
    $("#sponsor_select_button").click((e) => {
        e.preventDefault();
        var frame = wp.media({
            title: "Choose Sponsor Banner",
            button: {
                text: "Use this image"
            },
            library: {
                type: ["image/jpeg", "image/png", "image/gif"]
            },
            multiple: false
        });
        frame.open();
        frame.on("select", () => {
            var attachment = frame.state().get("selection").first().toJSON();
            $("#sponsor_banner_img").attr("src", attachment.url);
            $("#sponsor_image_url").val(attachment.id);
            $("#remove_sponsor_banner").show();
            $("#sponsor_banner_preview_section").show();
            if ($("#sponsor_banner_preview").length) {
                var attachment_url = attachment.url.split("uploads")[1];
                var new_preview_url = $("#sponsor_banner_preview").attr("src").split("&s")[0]+"&s="+attachment_url;
                $("#sponsor_banner_preview").attr("src", new_preview_url);
            }
        });
    });
    $("#remove_sponsor_banner").click((e) => {
        e.preventDefault();
        $("#sponsor_banner_img").attr("src", "");
        $("#sponsor_image_url").val("");
        $("#remove_sponsor_banner").hide();
        $("#sponsor_banner_preview_section").hide();
    });
    $("#default_sponsor_banner_button").click((e) => {
        e.preventDefault();
        var frame = wp.media({
            title: "Choose Default Sponsor Banner",
            button: {
                text: "Use this image"
            },
            library: {
                type: ["image/jpeg", "image/png", "image/gif"]
            },
            multiple: false
        });
        frame.open();
        frame.on("select", () => {
            var attachment = frame.state().get("selection").first().toJSON();
            $("#default_sponsor_banner_preview_img").attr("src", attachment.url);
            $("#default_sponsor_banner").val(attachment.id);
            $(".no_default_image").hide();
        });
    });
});