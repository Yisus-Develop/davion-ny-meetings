(function(){
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.dnm-tab');
    if (!btn) return;
    var root = btn.closest('.dnm-wrap');
    if (!root) return;
    var day = btn.getAttribute('data-day');
    root.querySelectorAll('.dnm-tab').forEach(function(t){ t.classList.remove('active'); });
    root.querySelectorAll('.dnm-slot-day').forEach(function(p){ p.classList.remove('active'); });
    btn.classList.add('active');
    var panel = root.querySelector('.dnm-slot-day[data-day-panel="' + day + '"]');
    if (panel) panel.classList.add('active');
  });
})();
