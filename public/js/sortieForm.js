document.addEventListener('DOMContentLoaded', function () {

    const lieuSelect = document.getElementById('createsortie_lieu');
    const villeSelect = document.getElementById('ville');

    const inputRue = document.getElementById('lieu-rue');
    const inputCp = document.getElementById('lieu-cp');
    const inputCoord = document.getElementById('lieu-coord');

    const allOptions = Array.from(lieuSelect.querySelectorAll('option'));

    villeSelect.addEventListener('change', function() {
        const selectedVille = this.value;

        lieuSelect.innerHTML = '<option value="" disabled selected>--Veuillez sélectionner un lieu--</option>';

        allOptions.forEach(option => {
            if (!option.value || option.dataset.ville === selectedVille) {
               const clone = option.cloneNode(true);
                lieuSelect.appendChild(clone);
            }
        });

    });

    // (édition)
    if (lieuSelect.value) {
        const selectedOption = lieuSelect.selectedOptions[0];
        if (selectedOption && selectedOption.dataset.ville) {
            villeSelect.value = selectedOption.dataset.ville;
            villeSelect.dispatchEvent(new Event('change'));
            lieuSelect.value = selectedOption.value;
        }
    }


    lieuSelect.addEventListener('change', function () {
        const lieuId = this.value;

        if (!lieuId) {
            inputRue.value = "";
            inputCp.value = "";
            inputCoord.value = "";
            return;
        }

        fetch(`/api/lieu/${lieuId}`)
            .then(res => res.json())
            .then(data => {
                inputRue.value = data.rue || "";
                inputCp.value = data.codePostal || "";
                inputCoord.value = (data.latitude && data.longitude)
                    ? `${data.latitude}, ${data.longitude}`
                    : "";
            })
            .catch(err => console.error("Erreur API lieu :", err));
    });

});