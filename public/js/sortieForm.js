document.addEventListener('DOMContentLoaded', function () {

    const selectLieu = document.getElementById('createsortie_lieu');
    const inputRue = document.getElementById('lieu-rue');
    const inputCp = document.getElementById('lieu-cp');
    const inputCoord = document.getElementById('lieu-coord');

    selectLieu.addEventListener('change', function () {
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