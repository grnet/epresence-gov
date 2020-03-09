<script type="text/javascript">

    $(document).ready(function () {

        var DepartmentAdminOrg = $("#FieldDepartmentAdminOrg");
        var DepartmentAdminOrgNewField = $("#DepartmentAdminOrgNewField");
        var DepartmentAdminOrgNewContainer = $("#DepartmentAdminOrgNewContainer");



        var DepartmentAdminDep = $("#FieldDepartmentAdminDepart");
        var DepartmentAdminDepContainer = $("#DepartmentAdminDepartContainer");

        var DepartmentAdminDepNewField = $("#DepartmentAdminDepNewField");
        var DepartmentAdminDepNewContainer = $("#DepartmentAdminDepNewContainer");

        DepartmentAdminOrgNewContainer.hide();
        DepartmentAdminDepNewContainer.hide();

        DepartmentAdminOrg.select2({
            allowClear: true,
            placeholder: "{!!trans('users.selectInstitutionRequired')!!}"
        }).on("change", function () {
            update_da_inst_selection_ui(true);
        });

        update_da_inst_selection_ui(false);


        function update_da_inst_selection_ui(load_departments){

            if (DepartmentAdminOrg.val() === "other") {
                DepartmentAdminOrgNewContainer.show();
                DepartmentAdminDepContainer.hide();
                DepartmentAdminDepNewContainer.show();
                DepartmentAdminDep.val("other");
            }
            else if (DepartmentAdminOrg.val() > 0 && DepartmentAdminOrg.val() !== "other") {

                if(load_departments){
                    DepartmentAdminDep.select2("data", null, {allowClear: true}).load("/institutions/departments/" + DepartmentAdminOrg.val());
                }else{
                     if(DepartmentAdminDep.val() === "other"){
                        DepartmentAdminDepNewContainer.show();
                    }
                }

                DepartmentAdminOrgNewContainer.hide();
                DepartmentAdminDepContainer.show();

                if(DepartmentAdminDep.val() !== "other"){
                    DepartmentAdminDepNewContainer.hide();
                }
            }
            else {
                DepartmentAdminOrgNewContainer.hide();
                function_clear_dep_admin_selections();
                //Check what this was doing
                // $("#CoordDepartOrgNew").hide();
            }
        }


        if (DepartmentAdminOrg.length > 0) {
            DepartmentAdminDep.select2({placeholder: "{!!trans('users.selectInstitutionFirst')!!}"});
        } else {
            DepartmentAdminDep.select2({placeholder: "{!!trans('users.selectDepartment')!!}"});
        }


        DepartmentAdminDep.on("change", function () {

            if (DepartmentAdminDep.val() === "other") {
                DepartmentAdminDepNewContainer.show();
            } else if (DepartmentAdminDep !== "other" && DepartmentAdminDep.val() > 0) {
                DepartmentAdminDepNewContainer.hide();
            }
            else if (DepartmentAdminDep.val() === "") {
                DepartmentAdminDepNewContainer.hide();
            }
        });


        var newDepartmentAdminButton = $("#NewDepartmentAdminButton");
        var newDepartmentAdminModal = $("#DepartmentAdminModal");


//  Button Εισαγωγής Διαχειριστη Τμήματος

        newDepartmentAdminButton.click(function () {
            newDepartmentAdminModal.modal("show");
        });

// Close Event στο modal Διαχειριστή Τμήματος

        newDepartmentAdminModal.on("hidden.bs.modal", function () {
            function_clear_dep_admin_selections();
        });

        $("#NewAdminEdit").click(function () {
            newDepartmentAdminModal.modal("show");
        });

        function function_clear_dep_admin_selections(){

            //$('input:radio').prop("checked", false);

            if(DepartmentAdminOrg.length > 0){
                DepartmentAdminOrg.select2("data", null, {allowClear: true});
                DepartmentAdminDep.html('<option value=""></option>');
            }

            DepartmentAdminOrgNewContainer.hide();

           // $("#errorsDiv").hide();



            DepartmentAdminDep.select2("data", null, {allowClear: true});
            DepartmentAdminDep.select2({
                placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
            });
        }

    });

</script>