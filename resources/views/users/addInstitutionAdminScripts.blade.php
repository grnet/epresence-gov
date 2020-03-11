
<script type="text/javascript">

    $(document).ready(function () {

        var InstitutionAdminOrg = $("#FieldInstitutionAdminOrg");
        var InstitutionAdminOrgNewContainer = $("#InstitutionAdminOrgNewContainer");


        var InstitutionAdminDep = $("#FieldInstitutionAdminDepart");
        var InstitutionAdminDepContainer = $("#InstitutionAdminDepartContainer");
        var InstitutionAdminDepNewContainer = $("#InstitutionAdminDepNewContainer");
        

        InstitutionAdminOrgNewContainer.hide();
        InstitutionAdminDepNewContainer.hide();


        InstitutionAdminOrg.select2({
            allowClear: true,
            placeholder: "{!!trans('users.selectInstitutionRequired')!!}"
        }).on("change", function () {
            update_ia_inst_selection_ui(true);
        });

        update_ia_inst_selection_ui(false);


        function update_ia_inst_selection_ui(load_departments){
            if (InstitutionAdminOrg.val() === "other") {
                InstitutionAdminOrgNewContainer.show();
                InstitutionAdminDepContainer.hide();
                InstitutionAdminDepNewContainer.show();
            }
            else if (InstitutionAdminOrg.val() > 0 && InstitutionAdminOrg.val() !== "other") {
                if(load_departments){
                    InstitutionAdminDep.select2("data", null, {allowClear: true}).load("/institutions/departments/" + InstitutionAdminOrg.val());
                }else{
                   if(InstitutionAdminDep.val() === "other")
                       InstitutionAdminDepNewContainer.show();
                }
                InstitutionAdminOrgNewContainer.hide();
            }
            else {
                function_clear_inst_admin_selections();
            }
        }

        InstitutionAdminDep.select2({placeholder: "{!!trans('users.selectInstitutionFirst')!!}"});
        InstitutionAdminDep.on("change", function () {

            if (InstitutionAdminDep.val() === "other") {
                InstitutionAdminDepNewContainer.show();
            } else if (InstitutionAdminDep.val() !== "other" && InstitutionAdminDep.val() > 0) {
                InstitutionAdminDepNewContainer.hide();
            }
            else if (InstitutionAdminDep.val() === "") {
                InstitutionAdminDepNewContainer.hide();
            }
        });

        var NewInstitutionAdminButton = $("#NewInstitutionAdminButton");
        var NewInstitutionAdminModal = $("#InstitutionAdminModal");
        
        
//  Button Εισαγωγής Διαχειριστη οργανισμού

        NewInstitutionAdminButton.click(function () {
            NewInstitutionAdminModal.modal("show");
        });

// Close Event στο modal Διαχειριστή Τμήματος

        NewInstitutionAdminModal.on("hidden.bs.modal", function () {
            function_clear_inst_admin_selections();
        });


        function function_clear_inst_admin_selections(){

            //$('input:radio').prop("checked", false);

            InstitutionAdminOrg.select2("data", null, {allowClear: true});

            InstitutionAdminDep.html('<option value=""></option>');
            InstitutionAdminDep.select2("data", null, {allowClear: true});

            InstitutionAdminDep.select2({
                allowClear: true,
                placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
            });
        }

    });


</script>