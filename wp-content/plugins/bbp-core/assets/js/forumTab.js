function forumTab(forumEvent, forumContainer, tabId) {
    var container   = document.getElementById(forumContainer);
    var tabContents = container.getElementsByClassName("tab-content");
    var tabs        = container.getElementsByClassName("tab");
    
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = "none";
    }
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }

    document.getElementById(tabId).style.display = "block";
    forumEvent.currentTarget.classList.add("active");
}