window.onload = () => {
    // Gestion des liens "supprimer"
    let links = document.querySelectorAll("[data-delete]")
    console.log(links)

    // On boucle sur links
    for (link of links) {
        // on écoute le clic
        link.addEventListener("click", function(e){
            // On empêche le navigation
            e.preventDefault()

            // on demande confirmation
            if(confirm("Voulez-vous supprimer cet élément ?")){
                // On envoie une requête Ajax vers le href du lien avec la méthode DELETE
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    //on récupère la réponse en JSON
                    response => response.json()
                ).then(data => {
                    if(data.success)
                        this.parentElement.remove()
                    else
                        alert(data.error)
                }).catch(e=> alert(e))
            }
        
        })

    }
};



