(function(A){A.effects.fold=function(B){return this.queue(function(){var E=A(this),J=["position","top","left"];var G=A.effects.setMode(E,B.options.mode||"hide");var N=B.options.size||15;var M=!(!B.options.horizFirst);A.effects.save(E,J);E.show();var D=A.effects.createWrapper(E).css({overflow:"hidden"});var H=((G=="show")!=M);var F=H?["width","height"]:["height","width"];var C=H?[D.width(),D.height()]:[D.height(),D.width()];var I=/([0-9]+)%/.exec(N);if(I){N=parseInt(I[1])/100*C[G=="hide"?0:1]}if(G=="show"){D.css(M?{height:0,width:N}:{height:N,width:0})}var L={},K={};L[F[0]]=G=="show"?C[0]:N;K[F[1]]=G=="show"?C[1]:0;D.animate(L,B.duration/2,B.options.easing).animate(K,B.duration/2,B.options.easing,function(){if(G=="hide"){E.hide()}A.effects.restore(E,J);A.effects.removeWrapper(E);if(B.callback){B.callback.apply(E[0],arguments)}E.dequeue()})})}})(jQuery)