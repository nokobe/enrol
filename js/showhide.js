function showHideDivClass(className) {
	var elements = document.getElementsByTagName("div");
	console.log("here with "+className);
	for (var x=0; x < elements.length; x++) {
		name = elements[x].getAttribute("class");
		if (name == className) {
//			alert('found div(name='+name+'), display = '+elements[x].style.display);
			if (elements[x].style.display == 'none') {
				elements[x].style.display = 'block';
			} else {
				elements[x].style.display = 'none';
			}
		}
	}
}
