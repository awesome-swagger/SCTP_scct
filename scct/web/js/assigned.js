/**
 * Created by tzhang on 5/31/2017.
 */
$(function () {

    var assignedGV = $("#assignedGV");
    var assignedSection_SectionNumber = [];
    var assignedMap_MapGrid = [];

    $('#UnassignedButton').prop('disabled', false); //TO DISABLED

    //assigned Seachfilter listener
    $(document).off('keypress', '#assignedFilter').on('keypress', '#assignedFilter', function (e) {
        if (e.keyCode === 13 || e.keyCode === 10) {
            e.preventDefault();
            reloadAssignedGridView();
        }
    });
    $(document).off('change', '#assignPageSize').on('change', '#assignPageSize', function () {
        $('#assignedTableRecordsUpdate').val(true);
        reloadAssignedGridView();
    });

    //pagination listener on assigned page
    $(document).off('click', '#assignedPagination .pagination li a').on('click', '#assignedPagination .pagination li a', function (event) {
        event.preventDefault();
        var page = $(this).data('page') + 1; // Shift by one to 1-index instead of 0-index.

        $('#assignedPageNumber').val(page);
        var form = $("#AssignForm");
        $('#loading').show();
        $.pjax.reload({
            container: "#assignedGridview",
            timeout: 99999,
            url: form.attr("action"),
            type: "GET",
            data: form.serialize()
        }).done(function () {
        });
        $('#assignedGridview').on('pjax:success', function (event, data, status, xhr, options) {
            console.log("Success");
            $('#loading').hide();
        });
        $('#assignedGridview').on('pjax:error', function (event, data, status, xhr, options) {
            console.log("Error");
            //window.location.reload(); // Can't leave them stuck
        });
    });

    //expandable row column listener
    //$(document).off('kvexprow:toggle', "#assignedTable #assignedGV").on('kvexprow:toggle', "#assignedTable #assignedGV", function (event, ind, key, extra, state) {
      //assignedGV.on('kvexprow.toggle.kvExpandRowColumn', function (event, ind, key, extra, state) {
      assignedGV.on('kvexprow:toggle', function (event, ind, key, extra, state) {
        console.log('Toggled expand row');
        $(this).find("[data-key='"+key+"']").find('.unassignCheckbox input[type=checkbox]').prop('checked', false).prop('disabled', true);
        assignedMap_MapGrid =$("#assignedGridview #assignedGV").yiiGridView('getSelectedRows');
        console.log("assignedMap_MapGrid: " +assignedMap_MapGrid.length);

        //check to see if need to disable/enable add surveyor button
        if (assignedMap_MapGrid.length > 0){
          $("#UnassignedButton").prop('disabled', false);
        }else {
          $("#UnassignedButton").prop('disabled', true);
        }
    });

    // set constrains:
    $(document).off('click', '.unassignCheckbox input[type=checkbox]').on('click', '.unassignCheckbox input[type=checkbox]', function () {
        assignedMap_MapGrid = $("#assignedGridview #assignedGV").yiiGridView('getSelectedRows');
        if (assignedMap_MapGrid.length > 0) {
            $("#UnassignedButton").prop('disabled', false);
        } else
            $("#UnassignedButton").prop('disabled', true);
        console.log(assignedMap_MapGrid);
    });

    //checkbox listener on section table
    $(document).off('click', '.assignedSectionCheckbox input[type=checkbox]').on('click', '.assignedSectionCheckbox input[type=checkbox]', function () {
        assignedSection_SectionNumber =$("#assignedGridview #assignedSectionGV").yiiGridView('getSelectedRows');
        /*assignedMap_MapGrid = $("#assignedGridview #assignedGV").yiiGridView('getSelectedRows');*/
        // check to see if need to disable/enable add surveyor button
        if (assignedMap_MapGrid.length > 0 || assignedSection_SectionNumber.length > 0){
            $("#UnassignedButton").prop('disabled', false);
        }else{
            $("#UnassignedButton").prop('disabled', true);
        }
        console.log(assignedSection_SectionNumber);
    });

    if (assignedMap_MapGrid.length > 0 || assignedSection_SectionNumber.length > 0)
        unassignCheckboxListener(assignedMap_MapGrid, assignedSection_SectionNumber);

    $(document).off('click', '#UnassignedButton').on('click', '#UnassignedButton', function () {
        console.log(getSelectedUserName(assignedMap_MapGrid, assignedSection_SectionNumber));
        $("#unassigned-message").find('span').html(getSelectedUserName(assignedMap_MapGrid, assignedSection_SectionNumber));
        $("#unassigned-message").css("display", "block");
        // When the user clicks buttons, close the modal
        $('#unassignedCancelBtn').click(function () {
            $("#unassigned-message").css("display", "none");
        });
        $('#unassignedConfirmBtn').click(function () {
            $("#unassigned-message").css("display", "none");
            unassignButtonListener(assignedMap_MapGrid, assignedSection_SectionNumber);
        });
    });
});

function unassignButtonListener(assignedMap_MapGrid, assignedSection_SectionNumber) {
    var pks = $("#assignedGridview #assignedGV").yiiGridView('getSelectedRows');
    var assignedGV = "assignedGV";
    var assignedSectionGV = "assignedSectionGV";
    var mapGridAssignedUsers = getUIDList(assignedMap_MapGrid, assignedGV);
    console.log(mapGridAssignedUsers);
    var mapGridSectionAssignedUsers = getUIDList(assignedSection_SectionNumber, assignedSectionGV);
    unassignMapData = getAssignedMapArray(assignedMap_MapGrid, mapGridAssignedUsers[0]);
    unassignSectionData = getAssignedSectionArray(assignedSection_SectionNumber, mapGridSectionAssignedUsers[0]);
    console.log("Checked Rows " + pks);
    var form = $("#AssignForm");
    $('#loading').show();
    $.ajax({
        url: '/dispatch/assigned/unassign',
        //data: {MapGrid: pks, AssignedToIDs: getAssignedUserIDs()},
        data: {unassignMap: unassignMapData, unassignSection: unassignSectionData},
        type: 'POST',
        beforeSend: function () {
            $('#loading').show();
        }
    }).done(function () {
        $.pjax.reload({
            container: '#assignedGridview',
            timeout: 99999,
            type: 'GET',
            url: form.attr("action"),
            data: form.serialize()
        });
        $('#assignedGridview').on('pjax:success', function () {
            $('#loading').hide();
            unassignCheckboxListener();
        });
        $('#assignedGridview').on('pjax:error', function (e) {
            e.preventDefault();
        });
    });

    // disable remove surveyor button again
    $('#UnassignedButton').prop('disabled', true); //TO DISABLED
}

function unassignCheckboxListener() {
    $(".unassignCheckbox input[type=checkbox]").click(function () {
        var pks = $("#assignedGridview #assignedGV").yiiGridView('getSelectedRows');
        if (!pks || pks.length != 0) {
            $('#UnassignedButton').prop('disabled', false); //TO ENABLE
        } else {
            $('#UnassignedButton').prop('disabled', true);
        }
    });
}

function reloadAssignedGridView() {
    var jqAssignedDropDowns = $('#assignedDropdownContainer');
    var form = jqAssignedDropDowns.find("#AssignForm");
    if (form.find(".has-error").length) {
        return false;
    }
    $('#loading').show();
    $.pjax.reload({
        type: 'GET',
        url: form.attr("action"),
        container: '#assignedGridview', // id to update content
        data: form.serialize(),
        timeout: 99999
    }).done(function () {
        $('#loading').hide();
        $('#addSurveyor').prop('disabled', true);
        $('#UnassignedButton').prop('disabled', true);
        $('#assignedTableRecordsUpdate').val(false);
        unassignCheckboxListener();
    });
}

function getAssignedUserIDs() {
    var pks = $('#assignedGV').yiiGridView('getSelectedRows');
    //alert("pks length is ：　"+pks.length);
    var AssignedToIDs = [];
    for(var i=0; i < pks.length; i++){
        // get assignedUserID associate with current MapGrid
        AssignedToIDs[i] = $(".kv-row-select input[MapGrid="+pks[i]+"]").attr("AssignedToID");
    }
    return AssignedToIDs;
}

// Generate Assigned Map Array;
function getAssignedMapArray(assignedMap_MapGrid, assignedUserID) {
    var mapGridArray = [];
    if (assignedMap_MapGrid.length > 0){
        for (var i = 0; i < assignedMap_MapGrid.length; i++){
            mapGridArray.push({
                MapGrid: assignedMap_MapGrid[i],
                AssignedUserID: assignedUserID
            })
        }
        return mapGridArray;
    }else{
        return mapGridArray;
    }
}

// Get Associated UIDList
function getUIDList(selectedKeys, gridViewID) {
    var UIDList = [];
    var condition = "";
    if (gridViewID == "assignedGV")
        condition = "MapGrid=";
    else
        condition = "SectionNumber=";

    if (selectedKeys.length > 0){
        for (var i = 0; i < selectedKeys.length; i++){
            var UID = $("#assignedGridview #"+gridViewID+" input["+condition + selectedKeys[i] + "]").attr("AssignedToID");
            UIDList.push(UID);
        }
        return UIDList;
    }else{
        return UIDList;
    }
}

// Generate Assigned Section Array;
function getAssignedSectionArray(assignedSection_SectionNumber, assignedUserID) {
    var assignedSectionArray = [];
    if (assignedSection_SectionNumber.length > 0) {
        for (var i = 0; i < assignedSection_SectionNumber.length; i++) {
            var MapGrid = $("#assignedGridview #assignedSectionGV input[SectionNumber=" + assignedSection_SectionNumber[i] + "]").attr("MapGrid");
            assignedSectionArray.push({
                MapGrid: MapGrid,
                SectionNumber: assignedSection_SectionNumber[i],
                AssignedUserID: assignedUserID
            });
        }
        return assignedSectionArray;
    }else{
        return assignedSectionArray;
    }
}

// Generate unAssign Data Array; combine mapGrid and section level
function getSelectedUserName(assignedMap_MapGrid, assignedSection_SectionNumber) {
    var selectedMapGridUser = "";
    var selectedSectionUser = "";
    for (var i = 0; i < assignedMap_MapGrid.length; i++){
        var userName_MapGrid = $("#assignedGridview #assignedGV input[MapGrid=" + assignedMap_MapGrid[i] + "]").attr("UserName");
        selectedMapGridUser += "<li>"+assignedMap_MapGrid[i] + " : " + userName_MapGrid+"</li>"
    }
    for (var j = 0; j < assignedSection_SectionNumber.length; j++){
        var userName_Section = $("#assignedGridview #assignedSectionGV input[SectionNumber=" + assignedSection_SectionNumber[j] + "]").attr("UserName");
        var mapGrid_Section = $("#assignedGridview #assignedSectionGV input[SectionNumber=" + assignedSection_SectionNumber[j] + "]").attr("MapGrid");
        selectedSectionUser += "<li>"+mapGrid_Section+" : "+userName_Section+"</li>"
    }
    var selectedUserNameList = "<ul>"+selectedMapGridUser+selectedSectionUser+"</ul>";
    return selectedUserNameList;
}