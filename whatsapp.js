function sendToWhatsapp(){
    let number = "+254724019643";
    let name = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let message = document.getElementById("message").value;

    var url = "https://api.whatsapp.com/send?phone=" + number + "&text=";
    + "Name: " + name + "%0a"
    + "Email: " + email + "%0a"
    + "Message: " + message + "%0a";

    window.open(url, '_blank');
    // Send the data to WhatsApp    

}