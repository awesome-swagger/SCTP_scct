// BULK DELETE
$(function () {

    // disable single approve button once user clicked it
    $('#enable_single_approve_btn_id_timecard').click(function (e) {
        $(this).addClass('disabled');
    });

    $('#multiple_approve_btn_id').prop('disabled', false); //TO DISABLED

    $(document).off('click', "#timeCardGV input[type=checkbox]").on('click', "#timeCardGV input[type=checkbox]", function (e) {
        var disable;
        if ($("#timeCardGV .kv-row-select input:checked").length != 0) {
            disable = false;
            $("#timeCardGV .kv-row-select input:checked").each(function () {
                if ($(this).attr("approved").toUpperCase() == "YES" || $(this).attr("totalworkhours") == 0) {
                    //disable = true;
                }
            });
        } else {
            disable = true;
        }
        if (disable) {
            $('#multiple_approve_btn_id').prop('disabled', true);
            $('#export_timecard_btn').prop('disabled', true);
        } else {
            $('#multiple_approve_btn_id').prop('disabled', false); //TO ENABLE
            $('#export_timecard_btn').prop('disabled', false); //TO ENABLE
        }
    });

    applyTimeCardOnClickListeners();
    applyTimeCardSubmitButtonListener();

});

function applyTimeCardOnClickListeners() {
    $('#multiple_approve_btn_id').click(function () {
        var primaryKeys = $('#GridViewForTimeCard').yiiGridView('getSelectedRows');
        var quantifier = "";

        if(primaryKeys.length <= 1 ) { // We don't expect 0 or negative but we need to handle it
            quantifier = "this item?";
        } else {
            quantifier = "these items?"
        }
        var confirmBox = confirm('Are you sure you want to approve ' + quantifier);
        if (confirmBox) {
            $.ajax({
                type: 'POST',
                url: '/time-card/approve-multiple',
                data: {
                    timecardid: primaryKeys
                }
            });
            /*var win = window.open('/time-card/download-time-card-data?selectedTimeCardIDs='+primaryKeys, '_blank');
            if (win) {

                $.pjax.reload({
                    type: 'GET',
                    url: '/time-card/ftp-files',
                    container: '#timeCardGridview',
                    timeout: 99999,
                    push: false,
                    replace: false,
                    replaceRedirect: false
                });
                $('#timeCardGridview').on('pjax:success', function () {
                    var payroll = window.open('/time-card/download-payroll-data?selectedTimeCardIDs=' + primaryKeys, '_blank');
                    if (payroll) {
                        $.ajax({
                            type: 'POST',
                            url: '/time-card/ftp-files-payroll'
                        });
                    } else {
                        //Browser has blocked it
                        console.log('Please allow popups for this website');
                    }
                    $('#multiple_approve_btn_id').prop('disabled', true); //TO DISABLED
                });
                $('#timeCardGridview').on('pjax:error', function () {
                    var payroll = window.open('/time-card/download-payroll-data?selectedTimeCardIDs=' + primaryKeys, '_blank');
                    if (payroll) {
                        $.ajax({
                            type: 'POST',
                            url: '/time-card/ftp-files-payroll'
                        });
                    } else {
                        //Browser has blocked it
                        console.log('Please allow popups for this website');
                    }
                    $('#multiple_approve_btn_id').prop('disabled', true); //TO DISABLED
                });
            }*/
        } else {
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    });
}

function applyTimeCardSubmitButtonListener() {
    $('#multiple_submit_btn_id').click(function () {
        var quantifier = "";
        var name = 'timecard_history_';
        var thIndex = $('th:contains("Project Name")').index();
        var projectName = $('table td').eq(thIndex).text();
        var d = new Date();
        var minutes = (d.getMinutes() < 10 ? "0" : "") + d.getMinutes();
        var hours = ((d.getHours() + 11) % 12 + 1);
        dates = $.datepicker.formatDate('yy-mm-dd', new Date());

        timeCardName = name+dates+"_"+hours+"_"+minutes+"_"+d.getSeconds()

        console.log(projectName);
        console.log(timeCardName);

        //return false;
        var primaryKeys = $('#GridViewForTimeCard').yiiGridView('getSelectedRows');
        if(primaryKeys.length <= 1 ) { // We don't expect 0 or negative but we need to handle it
            quantifier = "this item?";
        } else {
            quantifier = "these items?"
        }
        var confirmBox = confirm('Are you sure you want to approve ' + quantifier);
        if (confirmBox) {
            var primaryKeys = $('#GridViewForTimeCard').yiiGridView('getSelectedRows');
            /*$.ajax({
                type: 'POST',
                url: '/time-card/approve-multiple',
                data: {
                    timecardid: primaryKeys
                }
            });*/
            //FORCE CSV DOWNLOAD
            window.open('/time-card/download-time-card-data?timeCardName='+timeCardName+'&projectName=' + projectName, '_blank');
          


          //  if (win) {

                 /* $.pjax.reload({
                    type: 'GET',
                    url: '/time-card/ftp-files?timeCardName='+timeCardName,
                    container: '#timeCardGridview',
                    timeout: 99999,
                    push: false,
                    replace: false,
                    replaceRedirect: false
                });
               
               $('#timeCardGridview').on('pjax:success', function () {
                    var payroll = window.open('/time-card/download-payroll-data?selectedTimeCardIDs=' + primaryKeys, '_blank');
                    if (payroll) {
                        $.ajax({
                            type: 'POST',
                            url: '/time-card/ftp-files-payroll'
                        });
                    } else {
                        //Browser has blocked it
                        console.log('Please allow popups for this website');
                    }
                    $('#multiple_approve_btn_id').prop('disabled', true); //TO DISABLED
                });
               $('#timeCardGridview').on('pjax:error', function () {
                    var payroll = window.open('/time-card/download-payroll-data?selectedTimeCardIDs=' + primaryKeys, '_blank');
                    if (payroll) {
                        $.ajax({
                            type: 'POST',
                            url: '/time-card/ftp-files-payroll'
                        });
                    } else {
                        //Browser has blocked it
                        console.log('Please allow popups for this website');
                    }
                    $('#multiple_approve_btn_id').prop('disabled', true); //TO DISABLED
                });
           // } */
        } else {
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    });
}

/*function appendQueryString(url, queryVars) {
    var firstSeperator = (url.indexOf('?')==-1 ? '?' : '&');
    var queryStringParts = new Array();
    for(var key in queryVars) {
        queryStringParts.push(key + '=' + queryVars[key]);
    }
    var queryString = queryStringParts.join('&');
    return url + firstSeperator + queryString;
}*/


/*Converts javascript array to CSV format */
function ConvertToCSV(headerArray, dataArray) {
    var header = typeof headerArray != 'object' ? JSON.parse(headerArray) : headerArray;
    var array = typeof dataArray != 'object' ? JSON.parse(dataArray) : dataArray;

    /* Get table headers */
    var indexes = [];
    for (var i = 0; i < header.length; i++) {
        str += '<th scope="col">' + header[i]['title'] + '</th>';
        indexes.push(header[i]['title']);
    }

    /* Data */
    var str = '';
    var strIndexes = '';

    /* Write headers */
    for (var j = 0; j < indexes.length; j++) {
        if (j != indexes.length - 1 && isNaN(indexes[j])) {
            strIndexes += indexes[j] + ',';
        }
        else
            strIndexes += indexes[j];
    }
    str += strIndexes + '\r\n';

    /* write data */
    for (var i = 0; i < array.length; i++) {
        var line = '';
        for (var index in array[i]) {
            if (line != '') line += ','

            line += array[i][index];
        }

        str += line + '\r\n';
    }

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var yyyy = today.getFullYear();

    if (dd < 10) {
        dd = '0' + dd
    }

    if (mm < 10) {
        mm = '0' + mm
    }
    today = mm + '/' + dd + '/' + yyyy;

    if (navigator.userAgent.indexOf('Firefox') != -1 && parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf('Firefox') + 8)) >= 3.6) {//Firefox

        var csvContent = "data:text/csv;charset=utf-8," + str;
        var encodedUri = encodeURI(csvContent);
        window.open(encodedUri);

    } else if (navigator.userAgent.indexOf('Chrome') != -1 && parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf('Chrome') + 7).split(' ')[0]) >= 15) {//Chrome

        var csvContent = "data:text/csv;charset=utf-8," + str;
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);


        link.setAttribute("download", "Report_" + today + ".csv");

        link.click(); // This will download the data file named "my_data.csv".
    } else if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Version') != -1 && parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf('Version') + 8).split(' ')[0]) >= 5) {//Safari

    } else {
        str = str.replace(/[^\x00-\x7F]/g, "");
        var csvContent = str; //here we load our csv data
        var blob = new Blob([csvContent], {
            type: "text/csv;charset=utf-8;"
        });

        var date = new Date();
        navigator.msSaveBlob(blob, "Report_" + today + ".csv")
    }
}