$(document).ready(function() {
    function ochrana(){
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0].type === 'back_forward')) {
                window.location.reload();
            }
        });
    }
    function vytvorRadek(rowClass, index, html) {
        return $("<tr>")
            .addClass(rowClass)
            .attr("data-index", index)
            .html(html);
    }
    function closeModal() {
        if($(".modal").is(":visible")) {
            $(".modal").fadeOut(200).css("display", "none");
        }
    }
    function initializeDatepicker(selector) {
        $('.date').attr('autocomplete', 'off');
        
        $(selector).datepicker({
            dateFormat: 'dd. mm. yy',
            firstDay: 1, // Pondělí jako první den
            dayNames: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
            dayNamesMin: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
            dayNamesShort: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
            monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
            monthNamesShort: ['Led', 'Úno', 'Bře', 'Dub', 'Kvě', 'Čer', 'Čec', 'Srp', 'Zář', 'Říj', 'Lis', 'Pro'],
            showWeek: true,
            weekHeader: 'Týden'
        });
    }
    function initializeRange(selector, output, input) {
        $(selector).slider({
            min: 1,
            max: 10,
            step: 1,
            value: $(selector).val() || 5,
            slide: function(event, ui) {
                $(output).text(ui.value);
                $(input).val(ui.value);
                $(this).val(ui.value);
            }
        });
        $(selector).on("touchstart touchmove", function(e) {
            e.preventDefault();
            
            const touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
            const slider = $(this);
            const offset = slider.offset();
            const width = slider.width();
            const x = touch.pageX - offset.left;
            
            const min = slider.slider("option", "min");
            const max = slider.slider("option", "max");
            let value = Math.round((x / width) * (max - min)) + min;
            value = Math.min(Math.max(value, 1), 10);
            
            slider.slider("value", value);
            $(output).text(value);
            $(input).val(value);
        });
    }
    function initializeTitle(selector) {
        $(selector).tooltip({
            position: {
                my: "center bottom-10",
                at: "center top"
            },
            content: $(this).attr("title"), 
            show: {
                effect: "fadeIn",
                duration: 200
            },
            hide: {
                effect: "fadeOut",
                duration: 200
            }
        });
    }
    ochrana();
    initializeDatepicker('.date');
    //initializeRange('#riziko', '#rizikoValue', '#rizikoInput');
    initializeTitle("input");
    initializeTitle("textarea");


    if($(".respons").css("display") == "none"){
        $(".respons input, .respons textarea").each(function() {
            $(this).attr("disabled", true);
        });
    }
    else{
        $(".origo input, .origo textarea").each(function() {
            $(this).attr("disabled", true);
        });
    }

    $(document).on('click', '#closeBtn', closeModal);

    $(document).on('click', '#logout', function() {
        if (confirm("Opravdu se chcete odhlásit?")) {
            $.ajax({
                url: "login.php",
                type: "POST",
                data: { action: "logout" },
                success: function() {
                    window.location.replace("login.php");
                },
            });
        }
    });

    $(document).on('focus', '.date', function () {
        if (!$(this).hasClass('hasDatepicker')) {
            initializeDatepicker(this);
        }
    });

    $(document).on('click', '#odeslat', function() {
        const form = document.querySelector("#form");

        if (!form.checkValidity()) {
            form.reportValidity(); 
            return;
        }

        $("#form").find(".date").each(function() {
            const dateValue = $(this).val();
            if (dateValue) {
                const dateParts = dateValue.split('.');
                const dateFinal = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                $(this).val(dateFinal);
            }
        });
        
        const formData = $("#form").serializeArray();
        $.ajax({
            url: "sub_povoleni.php",
            type: "POST", 
            data: formData, 
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#modalOdeslano h2").text("Povolení č. " + response.data.ev_cislo);
                    $("#modalOdeslano input[type=hidden]").val(response.data.id);
                    $("#modalOdeslano").fadeIn(200).css("display", "flex");
                } else {
                    alert("Chyba při odesílání povolení: " + (response.message || "Neznámá chyba") + response.error);
                }
            },
            error: function(xhr, status, error) {
                alert("Chyba komunikace se serverem! (" + status + " " + error + " " + xhr.responseText + ")");
            }
        });
    });

    $(document).on('click', '.link', function () {
        const id = $(this).attr("id");
        $.ajax({
            url: "get_db.php",
            type: "POST",
            data: { id_spec: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $(".modal h2").text("Specifikace č. " + response.data.c_spec);
                    $(".modal .titr").text("<strong>" + response.data.titr + "</strong>");
                    $(".modal .sk_titr").text("<strong>" + response.data.titr_skup + "</strong>");
                    $(".modal .sk_stroj").text("<strong>" + response.data.sk_stroj + "</strong>");
                    $(".modal .zadal").html("<strong>" + response.data.zadal + "</strong>");
                    $(".modal .vytvoreno").text(response.data.vytvoreno);
                    $(".modal .upraveno").text(response.data.upraveno);
                    $(".modal .poznamka").text(response.data.poznamka || "Žádná");
                    $(".modal input[type='hidden']").val(id);
                    $(".modal").fadeIn(200).css("display", "flex");
                } else {
                    alert("Chyba při načítání dat!");
                    alert(response.message);
                }
            },
            error: function() {
                alert("Chyba komunikace se serverem!");
            }
        });        
    }); 
    
    $(document).on('keydown', function (e) {
        if (e.key === "Escape") { 
            closeModal();
        }
    });
    
    // $(document).on('input', '#riziko', function () {
    //     $("#rizikoValue").text($(this).val());
    // });
    
    $(document).on('change', '.time', function() {
        const value = $(this).val();
        
        if (value.length == 2)
            $(this).val(value + ":00");
        else if(value.length == 1)
            $(this).val("0" + value + ":00");
    });
});