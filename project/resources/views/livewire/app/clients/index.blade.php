<?php

use Livewire\Volt\Component;

new class extends Component {
    public int $active = 0; // 0,1,2
    public int $total = 3;

    public function next()
    {
        $this->active = ($this->active + 1) % $this->total;
    }

    public function prev()
    {
        $this->active = ($this->active - 1 + $this->total) % $this->total;
    }

    public function goTo(int $index)
    {
        $this->active = $index;
    }
};
?>


<div class="container">

    <div class="aflak-head">
        <h2 class="accent-color">آراء عملائنا</h2>
        <p>تعرّف على ثقة المقاولين، الشركات، وأصحاب الورش في أفلاك للفولاذ—جودة وتوريد سريع حسب المواصفات.</p>
        <div class="aflak-top-glow"></div>
    </div>

    <div class="aflak-shell position-relative">

        <!-- Buttons -->
        {{-- <button class="aflak-nav prev" type="button" aria-label="السابق" wire:click="prev">
                <i class="fa-solid fa-chevron-right"></i>
            </button> --}}

        <div class="aflak-track" aria-label="سلايدر آراء العملاء">

            <!-- Card 1 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
                        <!-- بدّلي الصورة حسبك -->
                        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop"
                            alt="عميل 1">
                    </div>

                    <h5 class="aflak-name fs-5">محمود — مقاول</h5>
                    <p class="aflak-meta">توريد حديد تسليح لمشروع سكني</p>

                    <p class="aflak-quote ">
                        “التزام بالمقاسات والكمية، والتجهيز كان سريع جدًا. وفروا علينا وقت كبير في الموقع.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-shield-halved"></i> جودة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-ruler-combined"></i> دقة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-truck-fast"></i> سرعة</span>
                    </div>
                </div>
            </article>

            <!-- Card 2 (Center) -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
                        <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?q=80&w=1200&auto=format&fit=crop"
                            alt="عميل 2">
                    </div>

                    <h3 class="aflak-name">سارة — إدارة مشتريات</h3>
                    <p class="aflak-meta">صفائح ومواسير لمشروع تجاري</p>

                    <p class="aflak-quote ">
                        “التسعير واضح والتعامل راقي. أخذنا سماكات مختلفة ووصلت مرتبة وجاهزة للتنفيذ.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-tags"></i> تسعير واضح</span>
                        <span class="aflak-chip"><i class="fa-solid fa-check"></i> التزام</span>
                        <span class="aflak-chip"><i class="fa-solid fa-headset"></i> دعم</span>
                    </div>
                </div>
            </article>

            <!-- Card 3 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
                        <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?q=80&w=1200&auto=format&fit=crop"
                            alt="عميل 3">
                    </div>

                    <h3 class="aflak-name">علي — صاحب ورشة</h3>
                    <p class="aflak-meta">قص وتجهيز حسب الطلب</p>

                    <p class="aflak-quote ">
                        “خدمة القص ممتازة—المقاسات طلعت دقيقة وقل الهدر بشكل واضح. أنصح فيهم.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-scissors"></i> قص</span>
                        <span class="aflak-chip"><i class="fa-solid fa-cube"></i> تجهيز</span>
                        <span class="aflak-chip"><i class="fa-solid fa-thumbs-up"></i> ثقة</span>
                    </div>
                </div>
            </article>


            <!-- Card 1 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
                        <!-- بدّلي الصورة حسبك -->
                        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=1200&auto=format&fit=crop"
                            alt="عميل 1">
                    </div>

                    <h5 class="aflak-name fs-5">محمود — مقاول</h5>
                    <p class="aflak-meta">توريد حديد تسليح لمشروع سكني</p>

                    <p class="aflak-quote ">
                        “التزام بالمقاسات والكمية، والتجهيز كان سريع جدًا. وفروا علينا وقت كبير في الموقع.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-shield-halved"></i> جودة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-ruler-combined"></i> دقة</span>
                        <span class="aflak-chip"><i class="fa-solid fa-truck-fast"></i> سرعة</span>
                    </div>
                </div>
            </article>

            <!-- Card 2 (Center) -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
                        <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?q=80&w=1200&auto=format&fit=crop"
                            alt="عميل 2">
                    </div>

                    <h3 class="aflak-name">سارة — إدارة مشتريات</h3>
                    <p class="aflak-meta">صفائح ومواسير لمشروع تجاري</p>

                    <p class="aflak-quote ">
                        “التسعير واضح والتعامل راقي. أخذنا سماكات مختلفة ووصلت مرتبة وجاهزة للتنفيذ.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-tags"></i> تسعير واضح</span>
                        <span class="aflak-chip"><i class="fa-solid fa-check"></i> التزام</span>
                        <span class="aflak-chip"><i class="fa-solid fa-headset"></i> دعم</span>
                    </div>
                </div>
            </article>

            <!-- Card 3 -->
            <article class="aflak-card">
                <div class="aflak-inner">
                    <div class="aflak-stars" aria-label="تقييم 5 من 5">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i>
                    </div>

                    <div class="aflak-photo">
                        <img src="https://images.unsplash.com/photo-1556761175-b413da4baf72?q=80&w=1200&auto=format&fit=crop"
                            alt="عميل 3">
                    </div>

                    <h3 class="aflak-name">علي — صاحب ورشة</h3>
                    <p class="aflak-meta">قص وتجهيز حسب الطلب</p>

                    <p class="aflak-quote ">
                        “خدمة القص ممتازة—المقاسات طلعت دقيقة وقل الهدر بشكل واضح. أنصح فيهم.”
                    </p>

                    <div class="aflak-chips">
                        <span class="aflak-chip"><i class="fa-solid fa-scissors"></i> قص</span>
                        <span class="aflak-chip"><i class="fa-solid fa-cube"></i> تجهيز</span>
                        <span class="aflak-chip"><i class="fa-solid fa-thumbs-up"></i> ثقة</span>
                    </div>
                </div>
            </article>
        </div>

        {{-- <button class="aflak-nav next" type="button" aria-label="التالي" wire:click="next">

                <i class="fa-solid fa-chevron-left"></i>
            </button> --}}

    </div>
</div>
