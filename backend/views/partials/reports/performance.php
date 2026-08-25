            <section class="reports-grid reports-grid--primary" aria-label="Product performance">
                <article class="report-panel">
                    <header class="panel-heading">
                        <div>
                            <p class="section-kicker">Product performance</p>
                            <h2>Top products</h2>
                        </div>
                        <span class="panel-meta">Top five by sold quantity</span>
                    </header>
                    <div class="table-scroll">
                        <table class="reports-table reports-table--cards">
                            <caption class="visually-hidden">Top products by sold quantity and revenue</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Rank</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">Sold</th>
                                    <th scope="col">Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsBody">
                                <tr class="table-state-row">
                                    <td colspan="4">Loading report data…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="report-panel">
                    <header class="panel-heading">
                        <div>
                            <p class="section-kicker">Inventory mix</p>
                            <h2 id="categoryHeading">Products by category</h2>
                        </div>
                        <span class="panel-meta">Catalog count and stock units</span>
                    </header>
                    <div id="categoryChart" class="category-chart" aria-labelledby="categoryHeading">
                        <div class="chart-state">Loading report data…</div>
                    </div>
                </article>
            </section>
