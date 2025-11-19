$(document).ready(function() {
    //#region Proměnné pro výpočty
    const z3 = 19;
    const z4 = 28;
    const z25 = 10;
    const z26 = 30;
    const z33 = 10;
    const z34 = 30;
    const z38 = 10;
    const z39 = 30;
    const z40 = 10;
    const z41 = 30;
    const z42 = 22;
    const z43 = 44;
    const z44 = 22;
    const z45 = 44;
    const z46 = 29;
    const z47 = 58;
    const z48 = 10;
    const z49 = 30;
    const z52 = 18;
    const z53 = 18;
    const z54 = 21;
    const z55 = 14;
    const z58 = 21;
    const z59 = 14;
    const ipohon = 1/30;
    
    let typ_stroje = $("#selectStroj").val();
    let nA;
    let vg1;
    let vg2;
    let vw;
    let vWT;
    let sg1g2;
    let sg2w;
    let sWT;
    let korekce_nove;
    let korekce_barmag;
    let produkce_1;
    //#endregion
    function ochrana(){
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0].type === 'back_forward')) {
                window.location.reload();
            }
        });
    }
    function isValidNumber(value) {
        return typeof value === "number" && !isNaN(value) && value !== Infinity && value !== -Infinity;
    }
    function pocitej(){
        $('#kotouce_div input').trigger('input');
        $('#galety_div input').trigger('input');
        $('#praci_valce_div input').trigger('input');
        $('#susici_valce_div input').trigger('input');
        $('#navijeni_div input').trigger('input');
        $('#cerpadlo_div input').trigger('input');
        $('#ukladani_div input').trigger('input');
    }
    function vytvorRadek(rowClass, index, html) {
        return $("<tr>")
            .addClass(rowClass)
            .attr("data-index", index)
            .html(html);
    }
    function closeModal() {
        if($(".modal").is(":visible")) {
            if(window.location.href.includes("spec_form.php")){
                window.location.replace("specifikace.php");
                return;
            }
            $(".modal").fadeOut(200);
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
                    $("#modalOdeslano h2").text("Specifikace č. " + response.data.c_spec);
                    $("#modalOdeslano input[type=hidden]").val(response.data.id_spec);
                    $("#modalOdeslano").fadeIn(200).css("display", "flex");
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
                    $(".modal input[name='id']").val(id);
                    $(".modal input[name='typ_stroje']").val(response.data.sk_stroj);
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
    $(document).on('blur', '#selectVyr', function() {
        location.reload();
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
        const npohon = hnaci_motor * kotouc1 / kotouc2;
        nA = npohon * z3 / z4;

        if(isValidNumber(npohon)){
            $("#npohon").val(npohon.toFixed(2));
            $("#nA").val(nA.toFixed(2));
        }
    });
    $(document).on('input', '#galety_div input', function() {
        const galety = $("#galety").val();
        const z13 = $("#Z13").val();
        const z14 = $("#Z14").val();
        const z15 = $("#Z15").val();
        const z16 = $("#Z16").val();
        const z30 = $("#Z30").val();
        const z32 = $("#Z32").val();
        let ng1, ng2;
        if(typ_stroje == "3"){
            vg2 = parseFloat($("#vg2").val());
            sg1g2 = parseFloat($("#dlouzeni_div_nove #SG1-G2").val())/100;
            const titr = $("#titr").val();
            const pocet_sprad_mist = $("#pocet_sprad_mist").val();
            ng2 = vg2 / (galety / 1000 * Math.PI);
            ng1 = ng2 / (1 + sg1g2)
            vg1 = ng1 * galety / 1000 * Math.PI;
            produkce_1 = vg2 * 60 / 10000 * titr / 1000;
            const produkce_stroj = produkce_1 * pocet_sprad_mist;

            if(isValidNumber(ng2)){
                $("#nG2").val(ng2.toFixed(2));
            }
            $("#produkce_1_misto").val(produkce_1.toFixed(2));
            $("#produkce_stroj").val(produkce_stroj.toFixed(2));
        }else{
            ng2 = nA * z13 / z14 * z15 / z16 * z33 / z34 * z53 / z52;
            ng1 = ng2 * z30 / z32;
            vg2 = ng2 * galety / 1000 * Math.PI;
            vg1 = ng1 * galety / 1000 * Math.PI;
            sg1g2 = (ng2 - ng1) / ng1 * 100;
            console.log(sg1g2);
            
            if(isValidNumber(ng2)){
                $("#nG2").val(ng2.toFixed(2));
                $("#vG2").val(vg2.toFixed(2));
            }
            if(isValidNumber(sg1g2)){
                $("#dlouzeni_div_stare #SG1-G2").val(sg1g2.toFixed(2));
            }
        }
        if(isValidNumber(ng1)){
            $("#nG1").val(ng1.toFixed(2));
            $("#vG1").val(vg1.toFixed(2));
        }

    });
    $(document).on('input', '#praci_valce_div input', function() {
        const praci_valce = $("#praci_valce").val();
        const Z9 = $("#Z9").val();
        const Z10 = $("#Z10").val();
        const Z11 = $("#Z11").val();
        const Z12 = $("#Z12").val();
        
        if(typ_stroje == "3"){
            sg2w = parseFloat($("#dlouzeni_div_nove #SG2-W").val())/100;
            vw = vg2 * (1 + sg2w);
            const nw = vw / (praci_valce / 1000 * Math.PI);

            if(isValidNumber(nw)){
                $("#nW").val(nw.toFixed(2));
                $("#vW").val(vw.toFixed(2));
            }
        }
        else{
            const nw = nA * Z9 / Z10 * Z11 / Z12 * z25 / z26 * z59 / z58;
            vw = nw * praci_valce / 1000 * Math.PI;
            sg2w = (vw - vg2) / vg2 * 100;
            
            if(isValidNumber(nw)){
                $("#nW").val(nw.toFixed(2));
                $("#vW").val(vw.toFixed(2));
            }
            if(isValidNumber(sg2w)){
                $("#dlouzeni_div_stare #SG2-W").val(sg2w.toFixed(2));
            }
        }
    });
    $(document).on('input', '#susici_valce_div input', function() {
        const susici_valec = $("#susici_valec").val();
        const Z17 = $("#Z17").val();
        const Z18 = $("#Z18").val();
        const Z19 = $("#Z19").val();
        const Z20 = $("#Z20").val();

        if(typ_stroje == "3"){
            sWT = parseFloat($("#dlouzeni_div_nove #SW-T").val())/100;
            vWT = vw * (1 + sWT);
            const nWT = vWT / (susici_valec / 1000 * Math.PI);
            const Sges = (vWT - vg1) / vg1 * 100;

            if(isValidNumber(nWT)){
                $("#nWT").val(nWT.toFixed(2));
                $("#vWT").val(vWT.toFixed(2));
                $("#dlouzeni_div_nove #Sges").val((Sges).toFixed(2));
            }
        }else{
            const nWT = nA * Z17 / Z18 * Z19 / Z20 * z38 / z39 * z55 / z54;
            vWT = nWT * susici_valec / 1000 * Math.PI;
            sWT = (vWT - vw) / vw * 100;
    
            if(isValidNumber(vWT)){
                $("#nWT").val(nWT.toFixed(2));
                $("#vWT").val(vWT.toFixed(2));
            }
            if(isValidNumber(sWT)){
                $("#dlouzeni_div_stare #SW-T").val(sWT.toFixed(2));
            }
        }
    });
    $(document).on('input', '#navijeni_div input', function() { 
        const navijeci_valec = $("#navijeci_valec").val();
        const dlouzeni = parseFloat($("#dlouzeni").val()) / 100 || 0;
        const vnavijeni = vWT * (1 + dlouzeni);
        const nnavijeni = vnavijeni / (navijeci_valec / 1000 * Math.PI);
        const n1pohon = nnavijeni * z49 / z48;
        const sges = (vnavijeni - vg1) / vg1 * 100;

        if(isValidNumber(vnavijeni)){
            $("#v_navijeni").val(vnavijeni.toFixed(2));
        } 
        if(isValidNumber(nnavijeni)){
            $("#n_navijeni").val(nnavijeni.toFixed(2));
            $("#1_pohon").val(n1pohon.toFixed(2));
        }
        if(isValidNumber(sges)){
            $("#dlouzeni_div_stare #Sges").val(sges.toFixed(2));
        }
    });
    $(document).on('input', '#cerpadlo_div input', function() {
        const cerpadlo = $("#cerpadlo").val();
        const pocet_sprad_mist = $("#pocet_sprad_mist").val();
        korekce_nove = parseFloat($("#korekce_nove").val()) / 100 || 0;
        korekce_barmag = parseFloat($("#korekce_barmag").val()) / 100 || 0;
        const Z21 = $("#Z21").val();
        const Z22 = $("#Z22").val();
        const Z23 = $("#Z23").val();
        const Z24 = $("#Z24").val(); 
        
        if(typ_stroje == "3"){
            const faktor = parseFloat($("#faktor").val());
            const spotreba_misto = produkce_1 / faktor * (1 + korekce_nove); 
            const spotreba_stroj = spotreba_misto * pocet_sprad_mist;
            const nsp = spotreba_misto * 1000 / 60 / cerpadlo; 
            const produkce_stroj = produkce_1 * pocet_sprad_mist;

            if(isValidNumber(nsp)){
                $("#nSp").val(nsp.toFixed(2));
                $("#spotr_misto").val(spotreba_misto.toFixed(2));
                $("#spotr_stroj").val(spotreba_stroj.toFixed(2));
            }
            $("#produkce_stroj").val(produkce_stroj.toFixed(2));
        }else{
            const nsp = nA * Z21 / Z22 * Z23 / Z24 * z40 / z41 * z42 / z43 * z44 / z45 * z46 / z47 * (1 + korekce_barmag);
            const spotreba_misto = nsp * cerpadlo * 60 / 1000;
            const spotreba_stroj = spotreba_misto * pocet_sprad_mist;
            if(isValidNumber(nsp)){
                $("#nSp").val(nsp.toFixed(2));
                $("#spotr_misto").val(spotreba_misto.toFixed(2));
                $("#spotr_stroj").val(spotreba_stroj.toFixed(2));
            }
        }
    });
    $(document).on('input', '#ukladani_div input', function() {
        const motor = $("#motor").val();
        const rs3 = $("#rs3").val();
        const rs4 = $("#rs4").val();
        const faktor = $("#faktor").val();

        const zdvihy = (motor * rs3 / rs4) * ipohon;
        if(isValidNumber(zdvihy)){
            $("#dvojite_zdvihy").val(zdvihy.toFixed(2));
        }
        $("#ipohon").val(ipohon.toFixed(5));
    });
    
    if(window.location.href.includes("print_form.php")){
        $("input").each(function() {
            const val = parseFloat($(this).val());
            if (!isNaN(val) && val % 1 !== 0) {
                $(this).val(val * 100 % 10 === 0 ? val.toFixed(1) : val.toFixed(2));
            }
        });
        if($("#nadpis").attr("data-typ") == "2"){
            $(".stare").each(function() {
                $(this).empty();
            });
        }
    }
    
    if(window.location.href.includes("spec_form.php"))
        pocitej();
});