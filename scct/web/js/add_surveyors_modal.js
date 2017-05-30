$(function(){

    var MapPlatArr = [];
    var IRUIDArr = [];

    //$('#addSurveyor').prop('disabled', true);
    //resetAddSurveyorButton(MapPlatArr, IRUIDArr);
    //resetAddSurveyorButtonDispatch(MapPlatArr, IRUIDArr);
});

function resetAddSurveyorButton(MapPlatArr, IRUIDArr){
    $(".Add input[type=checkbox] ").click(function(){

        var pks = $("#assignedGridview #assign").yiiGridView('getSelectedRows');

        if (pks != 0){
            $('#addSurveyor').prop('disabled', false);

            for (var i = 0; i < pks.length; i++) {
                // get approved value for this timecard
                MapPlatArr[i] = $(".Add input[AssignedWorkQueueUID=" + pks[i] + "]").attr("mapplat");
                console.log("MapPlat: "+MapPlatArr[i]);

                // get totalworkhours for this timecard
                IRUIDArr[i] = $(".Add input[AssignedWorkQueueUID=" + pks[i] + "]").attr("IRUID");
                console.log("IRUID: "+IRUIDArr[i]);
            }

            // triggered when checkbox selected
            if (MapPlatArr.length > 0 && IRUIDArr.length > 0) {
                $('#addSurveyorButton').click(function () {
                    // get the click of the create button
                    $('#addSurveyorModal').modal('show')
                        .find('#modalAddSurveyor')
                        .load('/dispatch/assigned/add-surveyor-modal', {
                            "mapplat[]": [MapPlatArr],
                            "IRUID[]": [IRUIDArr]
                        });
                });
            }
        }else {
            $('#addSurveyor').prop('disabled', true);
        }

    });
}

function resetAddSurveyorButtonDispatch(MapPlatArr, IRUIDArr){
    /*$(".Dispatch input[type=checkbox] ").click(function(){

        var pks = $("#dispatchUnassignedGridview #unassign").yiiGridView('getSelectedRows');

        if (pks != 0){
            $('#addSurveyor').prop('disabled', false);

            for (var i = 0; i < pks.length; i++) {
                // get approved value for this timecard
                MapPlatArr[i] = $(".Dispatch input[inspectionrequestuid=" + pks[i] + "]").attr("mapplat");
                console.log("MapPlat: "+MapPlatArr[i]);

                // get totalworkhours for this timecard
                IRUIDArr[i] = pks[i];
                console.log("IRUID: "+IRUIDArr[i]);
            }

            // triggered when checkbox selected
            if (MapPlatArr.length > 0 && IRUIDArr.length > 0) {
                $('#addSurveyorButtonDispatch').click(function () {
                    // get the click of the create button
                    $('#addSurveyorModal').modal('show')
                        .find('#modalAddSurveyor')
                        .load('/dispatch/assigned/add-surveyor-modal', {
                            "mapplat[]": [MapPlatArr],
                            "IRUID[]": [IRUIDArr]
                        });
                });
            }
        }else {
            $('#addSurveyor').prop('disabled', true);
        }

    });*/

    $('#addSurveyorButtonDispatch').click(function () {
        // get the click of the create button
        $('#addSurveyorModal').modal('show')
            .find('#modalAddSurveyor')
            .load('/dispatch/assigned/add-surveyor-modal', {
                "mapplat[]": [MapPlatArr],
                "IRUID[]": [IRUIDArr]
            });
    });
}