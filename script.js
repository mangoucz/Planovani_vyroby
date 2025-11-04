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

    if($("#selectStroj").length) { //nová spec.
        const stroj = $("#selectStroj").val();
        switch(stroj) {
            case "1": 
                $(".barmag").show();
                $(".stare").show();
                $(".nove").hide();
                break;
            case "2":
                $(".barmag").show();
                $(".stare").hide();
                $(".nove").hide();
                break;
            case "3":
                $(".barmag").hide();
                $(".stare").hide();
                $(".nove").show();
                //vg2 required
                break;
            default:
                break;
        }
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
            url: "sub_db.php",
            type: "POST", 
            data: formData, 
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // $("#modalOdeslano h2").text("Povolení č. " + response.data.ev_cislo);
                    // $("#modalOdeslano input[type=hidden]").val(response.data.id);
                    // $("#modalOdeslano").fadeIn(200).css("display", "flex");
                    alert("Specifikace byla úspěšně uložena.");
                } else {
                    alert("Chyba při odesílání specifikace:\n" +
                        (response.message || "Neznámá chyba") + "\n\n" +
                        JSON.stringify(response.error, null, 2)
                    );
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
                    $(".modal .titr").html("<strong>" + response.data.titr + "</strong>");
                    $(".modal .sk_titr").html("<strong>" + response.data.titr_skup + "</strong>");
                    $(".modal .sk_stroj").html("<strong>" + response.data.sk_stroj + "</strong>");
                    $(".modal .vytvoril").html("<strong>" + response.data.vytvoril + "</strong>");
                    $(".modal .vytvoreno").text(response.data.vytvoreno);
                    $(".modal .upraveno").text(response.data.upraveno);
                    $(".modal .vyrobek").text(response.data.vyrobek || "Nevybrán");
                    $(".modal .poznamka").text(response.data.poznamka || "-");
                    $(".modal input[type='hidden']").val(id);
                    $(".modal").fadeIn(200).css("display", "flex");
                } else {
                    alert("Chyba při načítání dat!");
                    alert(response.message);
                }
            },
            error: function() {
                alert("Chyba komunikace se serverem!");
                alert(xhr.responseText);
                alert(message);
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

    $(document).on('change', '#stroj', function() {
        
    });

    $(document).on('change', '#selectStroj', function() {
        const id_stroj = $(this).val();
        const url = new URL(window.location.href);
        url.searchParams.set('stroj', id_stroj);
        window.location.href = url.toString();
    });

    $(document).on('click', '.vyr', function() {
        const id_spec = $(this).attr("data-id_spec");
        const id_vyr = $(this).attr("id");
        const select = `<select name='id_vyr' id='selectVyr' data-id_spec='${id_spec}' style='padding: 7px'></select>`;
        $(this).replaceWith(select);
        $.ajax({
            url: "get_db.php",
            type: "POST",
            data: {get_vyrobky: true},
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    response.data.forEach(function(vyrobek) {
                        $("#selectVyr").append("<option value='" + vyrobek.id_vyr + "'" + (vyrobek.id_vyr == id_vyr ? ' selected' : '') + ">" + vyrobek.vyrobek + "</option>");
                    }); 
                    $("#selectVyr").append("<option value='0'" + (id_vyr == 0 ? 'selected' : '') + ">-- Nevybrán --</option>");
                    $("#selectVyr").focus();
                } else {
                    alert("Chyba při načítání výrobků: " + (response.message || "Neznámá chyba"));
                }
            },
            error: function() {
                alert("Chyba komunikace se serverem při načítání výrobků!");
            }
        });
    });

    $(document).on('change', '#selectVyr', function() {
        const id_spec = $(this).attr("data-id_spec");
        const selectedId = $(this).val();
        $.ajax({
            url: "sub_db.php",
            type: "POST",
            data: { id_spec: id_spec, id_vyr: selectedId },
            dataType: "json",
            success: function(updateResponse) {
                if (updateResponse.success) 
                    location.reload();
                else 
                    alert("Chyba při aktualizaci výrobku: " + (updateResponse.message || "Neznámá chyba"));
            },
            error: function() {
                alert("Chyba komunikace se serverem při aktualizaci výrobku!");
            }
        });
    });

    $(document).on('input', '#searchSpec', function() {
        const search = $(this).val();
        const typ_stroje = $("#selectStroj").val();

        $.ajax({
            url: "get_db.php",
            type: "POST",
            data: { search: search, typ: typ_stroje },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    const tbody = $("#body_spec");
                    tbody.empty();

                    response.data.forEach(function(spec) {
                        const rowHtml = `
                            <tr>
                                <td data-label='Č. spec.' id='c_spec'>${spec.c_spec}</td>
                                <td data-label='Titr' id='titr'>${spec.titr}</td>
                                <td data-label='Skup. titrů' id='skup_titr'>${spec.titr_skup}</td>
                                <td data-label='Skup. strojů' id='skup_stroj'>${spec.id_typ_stroje}</td>
                                <td data-label='Vytvořil' id='vytvoril'>${spec.vytvoril}</td>
                                <td data-label='Vytvořeno' id='vytvoreno'>${spec.vytvoreno}</td>
                                <td data-label='Výrobek' id='vyrobek'>
                                    <span class='vyr' id='${spec.id_vyr}' data-id_spec='${spec.id_spec}'>${spec.vyrobek}</span>
                                </td>
                                <td data-label='info' id='info'>
                                    <img src='info.png' alt='Podrobnosti' class='info-icon link' id='${spec.id_spec}'>
                                </td>
                            </tr>
                        `;
                        tbody.append(rowHtml);
                    });
                } else {
                    alert("Chyba při vyhledávání spec.: " + (response.message || "Neznámá chyba") + (response.error || ""));
                }
            },
            error: function() {
                $("#body_spec").empty();
            }
        });
    });

    $(document).on('input', '#kotouce_div input', function() {
        const hnaci_motor = $("#hnaci_motor").val();
        const kotouc1 = $("#kotouc1").val();
        const kotouc2 = $("#kotouc2").val();
        const kotouc3 = $("#kotouc3").val();
        const kotouc4 = $("#kotouc4").val();
        

    });
    $(document).on('input', '#galety_div input', function() {
        const galety = $("#galety").val();
        const Z13 = $("#Z13").val();
        const Z14 = $("#Z14").val();
        const Z15 = $("#Z15").val();
        const Z16 = $("#Z16").val();
        const Z30 = $("#Z30").val();
        const Z32 = $("#Z32").val();        
    });
    $(document).on('input', '#praci_valce_div input', function() {
        const praci_valce = $("#praci_valce").val();
        const Z9 = $("#Z9").val();
        const Z10 = $("#Z10").val();
        const Z11 = $("#Z11").val();
        const Z12 = $("#Z12").val();
        

    });
    $(document).on('input', '#susici_valce_div input', function() {
        const susici_valec = $("#susici_valec").val();
        const Z17 = $("#Z17").val();
        const Z18 = $("#Z18").val();
        const Z19 = $("#Z19").val();
        const Z20 = $("#Z20").val();
        

    });
    $(document).on('input', '#navijeni_div input', function() {
        const navijeci_valec = $("#navijeci_valec").val();
        const dlouzeni = $("#dlouzeni").val();

    });
    $(document).on('input', '#ukladani_div input', function() {
        const cerpadlo = $("#cerpadlo").val();
        const pocet_sprad_mist = $("#pocet_sprad_mist").val();
        const korekce = $("#korekce").val();
        const Z21 = $("#Z21").val();
        const Z22 = $("#Z22").val();
        const Z23 = $("#Z23").val();
        const Z24 = $("#Z24").val();        
    });
    $(document).on('input', '#praci_valce_div input', function() {
        const motor = $("#motor").val();
        const rs3 = $("#rs3").val();
        const rs4 = $("#rs4").val();
        const faktor = $("#faktor").val();
        const Z12 = $("#Z12").val();
        

    });
});